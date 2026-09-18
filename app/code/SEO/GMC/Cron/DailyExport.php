<?php
namespace SEO\GMC\Cron;

use SEO\GMC\Helper\GmcExportHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Psr\Log\LoggerInterface;

class DailyExport
{
    protected $collectionFactory;
    protected $productRepository;
    protected $helper;
    protected $websiteRepository;
    protected $logger;

    public function __construct(
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        GmcExportHelper $helper,
        WebsiteRepositoryInterface $websiteRepository,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->websiteRepository = $websiteRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $this->logger->info("GMC Daily TSV Cron Started");

            /** Load all websites */
            $websites = $this->websiteRepository->getList();

            $totalProductsAll = 0;
            $totalWebsites = 0;

            foreach ($websites as $website) {

                $websiteId = (int)$website->getId();

                /** Sanitize the website name */
                $websiteName = strtolower(
                    preg_replace('/[^a-zA-Z0-9_-]/', '_', $website->getName())
                );

                /** TSV filename */
                $fileName = 'gmc_product_' . $websiteName . '.tsv';

                /** Skip admin website */
                if ($fileName === 'gmc_product_admin.tsv') {
                    continue;
                }

                $filePath = BP . '/pub/' . $fileName;

                /** Build product collection */
                $collection = $this->collectionFactory->create();
                $collection->addAttributeToSelect('*');

                /** Visibility filter */
                $collection->addAttributeToFilter('visibility', [
                    'in' => [
                        Visibility::VISIBILITY_IN_CATALOG,
                        Visibility::VISIBILITY_IN_SEARCH,
                        Visibility::VISIBILITY_BOTH
                    ]
                ]);

                /** Filter by website */
                $collection->addWebsiteFilter($websiteId);

                $productCount = $collection->getSize();
                $totalProductsAll += $productCount;

                /** Load existing TSV rows */
                $existingRows = $this->helper->loadExistingTsv($filePath);

                /** Build rows */

                foreach ($collection as $prod) {
                    $product = $this->productRepository->getById($prod->getId());
                    $newRows = $this->helper->buildRow($product, $websiteId);

                    if (empty($newRows)) {
                        continue; // skip empty rows
                    }

                    // Flatten the array if it's nested (buildRow may return multiple rows for configurable)
                    foreach ($newRows as $row) {
                        $sku = $row[0] ?? null; // assume first column is SKU
                        if (!$sku) continue;

                        $existingRows[$sku] = $row; // add new or update existing
                    }
                }

                /** Save TSV */
                $this->helper->writeTsv($filePath, $existingRows);

                $totalWebsites++;
            }

            $this->logger->info(
                "Cron Export: Created TSV for {$totalWebsites} websites. Total products exported: {$totalProductsAll}"
            );

        } catch (\Exception $e) {
            $this->logger->error('Cron Error: ' . $e->getMessage());
        }
    }
}