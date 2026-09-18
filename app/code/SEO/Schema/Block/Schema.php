<?php
namespace SEO\Schema\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Output as CatalogOutputHelper;
use Magento\Directory\Model\Currency;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Schema extends Template
{
    protected $productRepository;
    protected $imageHelper;
    protected $reviewFactory;
    protected $outputHelper;
    protected $currency;
    protected $eavConfig;
    protected $storeManager;
    protected $priceCurrency;

    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        ReviewFactory $reviewFactory,
        CatalogOutputHelper $outputHelper,
        Currency $currency,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->reviewFactory = $reviewFactory;
        $this->outputHelper = $outputHelper;
        $this->currency = $currency;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;

        parent::__construct($context, $data);
    }

    /**
     * Get current product
     */
    public function getProduct()
    {
        try {
            $productId = (int)$this->getRequest()->getParam('id');
            if (!$productId) {
                return null;
            }

            return $this->productRepository->getById(
                $productId,
                false,
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Product image
     */
    public function getImage(Product $product)
    {
        return $this->imageHelper
            ->init($product, 'product_page_image_large')
            ->getUrl();
    }

    /**
     * Clean Page Builder description
     */
    protected function cleanDescription($description)
    {
        if (!$description) {
            return '';
        }

        // Remove Page Builder CSS blocks
        $description = preg_replace(
            '/#html-body\s*\[data-pb-style=[^\]]+\]\s*\{[^}]+\}/i',
            '',
            $description
        );

        // Remove any remaining Page Builder selectors
        $description = preg_replace(
            '/#html-body\s*\[data-pb-style=[^\]]+\]/i',
            '',
            $description
        );

        // Strip HTML tags
        $description = strip_tags($description);

        // Decode HTML entities
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');

        // Normalize whitespace
        $description = preg_replace('/\s+/', ' ', $description);

        return trim($description);
    }

    /**
     * Main schema output
     */
    public function getSchema()
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $rawDescription = $product->getDescription() ?: $product->getShortDescription();
        $cleanDescription = $this->cleanDescription($rawDescription);

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );

        $final = $this->convertPriceCurrency($product->getFinalPrice() ,$this->storeManager->getStore()->getId());

         $availability = $product->isAvailable()
        ? "https://schema.org/InStock"
        : "https://schema.org/OutOfStock";


        return [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => (string) $product->getName(),
            "sku" => (string) $product->getSku(),
            "description" => $cleanDescription,
            "image" => $this->getImage($product),
            "brand" => [
                "@type" => "Brand",
                "name" => "Otica Mart"
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => $product->getProductUrl(),
                "priceCurrency" => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                "price" => (float) number_format($final, 2),
                "availability" => $availability,
                "itemCondition" => "https://schema.org/NewCondition",
                "seller" => [
                    "@type" => "Organization",
                    "name" => "Otica Mart",
                    "url" => "https://oticamart.com/",
                    "logo" => "https://oticamart.com/media/logo/stores/7/otica-mart-logo_1.png"
                ]
            ]
        ];
    }

    public function convertPriceCurrency(float $amount, int $storeId): float
    {
        try {
            return (float) $this->priceCurrency->convert($amount, $storeId);
        } catch (\Throwable $e) {
            return $amount;
        }
    }
}