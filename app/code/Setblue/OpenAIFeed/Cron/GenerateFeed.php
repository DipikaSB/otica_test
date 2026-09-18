<?php
namespace Setblue\OpenAIFeed\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Review\Model\ReviewFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Model\Product\Visibility;

class GenerateFeed
{
    protected $productCollectionFactory;
    protected $scopeConfig;
    protected $directoryList;
    protected $file;
    protected $reviewFactory;
    protected $stockRegistry;
    protected $logger;
    protected $categoryRepository;
    protected $productRepository;
    protected $storeManager;
    protected $filterManager;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList,
        File $file,
        ReviewFactory $reviewFactory,
        StockRegistryInterface $stockRegistry,
        LoggerInterface $logger,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        FilterManager $filterManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->reviewFactory = $reviewFactory;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->filterManager = $filterManager;
    }

    public function execute()
    {
        try {
            $this->logger->info('OpenAI Feed cron STARTED');

            if (!$this->scopeConfig->getValue(
                'openai_feed/settings/enable',
                ScopeInterface::SCOPE_STORE
            )) {
                return;
            }

            // Admin context
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

            foreach ($this->storeManager->getStores(true) as $store) {

                if ($store->getId() == 0 || $store->getCode() === 'admin') {
                    continue;
                }

                $storeId   = (int)$store->getId();
                $storeCode = $store->getCode();
                $currency  = strtoupper($store->getCurrentCurrencyCode());

                $this->storeManager->setCurrentStore($storeId);

                $relativePath = $this->scopeConfig->getValue(
                    'openai_feed/settings/feed_path',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                ) ?: 'var/export/openai_{storeCode}_{currency}.json';

                $relativePath = str_replace(
                    ['{storeCode}', '{storeId}', '{currency}'],
                    [$storeCode, $storeId, strtolower($currency)],
                    $relativePath
                );

                $absolutePath = $this->directoryList->getRoot() . '/' . ltrim($relativePath, '/');
                $this->file->checkAndCreateFolder(dirname($absolutePath));

                // Seller info
                $seller = [
                    'seller_name'   => $this->scopeConfig->getValue('openai_feed/seller/seller_name', ScopeInterface::SCOPE_STORE, $storeId),
                    'seller_url'    => $this->scopeConfig->getValue('openai_feed/seller/seller_url', ScopeInterface::SCOPE_STORE, $storeId),
                    'return_policy' => $this->scopeConfig->getValue('openai_feed/seller/return_policy', ScopeInterface::SCOPE_STORE, $storeId),
                    'shipping'      => $this->scopeConfig->getValue('openai_feed/seller/shipping', ScopeInterface::SCOPE_STORE, $storeId),
                ];

                $collection = $this->productCollectionFactory->create();
                $collection->setStoreId($storeId);
                $collection->addStoreFilter($storeId);
                $collection->addAttributeToSelect('*');
                $collection->addAttributeToFilter('status', 1);
                $collection->addAttributeToFilter(
                    'visibility',
                    ['in' => [Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH]]
                );

                $mediaBaseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';

                $feedData = [];

                foreach ($collection as $product) {

                    $fullProduct = $this->productRepository->getById(
                        $product->getId(),
                        false,
                        $storeId
                    );

                    // Stock
                    $stock = $this->stockRegistry->getStockItem($product->getId());
                    $availability = ($stock && $stock->getIsInStock())
                        ? 'in_stock'
                        : 'out_of_stock';

                    // Images
                    $image = $fullProduct->getSmallImage();
                    $imageUrl = ($image && $image !== 'no_selection')
                        ? $mediaBaseUrl . '/' . ltrim($image, '/')
                        : '';

                    $additionalImages = [];
                    foreach ((array)$fullProduct->getMediaGalleryEntries() as $entry) {
                        if ($entry->getFile()) {
                            $additionalImages[] = $mediaBaseUrl . '/' . ltrim($entry->getFile(), '/');
                        }
                    }

                    // Categories
                    $categories = [];
                    foreach ((array)$fullProduct->getCategoryIds() as $categoryId) {
                        try {
                            $category = $this->categoryRepository->get($categoryId, $storeId);
                            if ($category->getLevel() > 1) {
                                $categories[] = $category->getName();
                            }
                        } catch (\Exception $e) {}
                    }

                    /**
                     * ===============================
                     * CLEAN PAGEBUILDER DESCRIPTION
                     * ===============================
                     */
                    $rawDescription = (string)$fullProduct->getDescription();

                    // Remove PageBuilder CSS blocks
                    $cleanDescription = preg_replace(
                        '/#html-body.*?}\s*/s',
                        '',
                        $rawDescription
                    );

                    // Strip HTML
                    $cleanDescription = $this->filterManager->stripTags(
                        $cleanDescription,
                        null,
                        true
                    );

                    // Decode entities
                    $cleanDescription = html_entity_decode($cleanDescription, ENT_QUOTES);

                    // Remove CRLF and normalize spaces
                    $cleanDescription = preg_replace("/\r\n|\r|\n/", ' ', $cleanDescription);
                    $cleanDescription = trim(preg_replace('/\s+/u', ' ', $cleanDescription));

                    /**
                     * ===============================
                     * STORE-AWARE PRICES (NUMERIC)
                     * ===============================
                     */
                    $price = (float)$fullProduct->getPriceInfo()
                        ->getPrice('regular_price')
                        ->getAmount()
                        ->getValue();

                    $finalPrice = (float)$fullProduct->getPriceInfo()
                        ->getPrice('final_price')
                        ->getAmount()
                        ->getValue();

                    // Reviews
                    $this->reviewFactory->create()->getEntitySummary($fullProduct, $storeId);
                    $rating = 0;
                    if ($fullProduct->getRatingSummary()) {
                        $rating = round($fullProduct->getRatingSummary()->getRatingSummary() / 20, 1);
                    }

                    $feedItem = [
                        'id'                    => $product->getSku(),
                        'title'                 => $product->getName(),
                        'description'           => $cleanDescription,
                        'link'                  => $fullProduct->getProductUrl(),
                        'condition'             => 'new',
                        'product_category'      => implode(', ', array_unique($categories)),
                        'brand'                 => $fullProduct->getAttributeText('brand'),
                        'image_link'            => $imageUrl,
                        'additional_image_link' => array_values(array_unique($additionalImages)),
                        'price'                 => number_format($price, 2, '.', ''),
                        'final_price'           => number_format($finalPrice, 2, '.', ''),
                        'currency'              => $currency,
                        'availability'          => $availability,
                        'seller_name'           => $seller['seller_name'],
                        'seller_url'            => $seller['seller_url'],
                        'return_policy'         => $seller['return_policy'],
                        'shipping'              => $seller['shipping'],
                        'product_review_rating' => $rating,
                        'product_review_count'  => (int)$fullProduct->getReviewsCount(),
                        'store_code'            => $storeCode,
                        'store_id'              => $storeId
                    ];

                    $feedData[] = array_filter($feedItem, function ($v) {
                        return !($v === null || $v === '' || $v === []);
                    });
                }

                $this->file->write(
                    $absolutePath,
                    json_encode($feedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );

                $this->logger->info("OpenAI Feed generated: {$absolutePath}");
            }

        } catch (\Exception $e) {
            $this->logger->critical('OpenAI Feed ERROR: ' . $e->getMessage());
        }
    }
}