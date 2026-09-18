<?php
namespace SEO\GMC\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use SEO\GMC\Helper\GmcExportHelper;
use Magento\Catalog\Model\Product\Visibility;

class MassExport extends Action
{
    protected $filter;
    protected $collectionFactory;
    protected $productRepository;
    protected $helper;
    protected $websiteRepository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        GmcExportHelper $helper,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->websiteRepository = $websiteRepository;
    }

    public function execute()
    {
        try {
            /** Get website ID */
            $websiteId = (int)$this->getRequest()->getParam('website_id');

            if (!$websiteId) {
                $this->messageManager->addErrorMessage("Website not selected!");
                return $this->_redirect('catalog/product/index');
            }

            /** Load website */
            $website = $this->websiteRepository->getById($websiteId);

            /** Sanitize website name */
            $websiteName = strtolower(
                preg_replace('/[^a-zA-Z0-9_-]/', '_', $website->getName())
            );

            /**
             * TSV file name:
             * gmc_product.tsv → gmc_product_<website>.tsv
             */
            $baseName = rtrim(GmcExportHelper::TSV_FILE, '.tsv');
            $filePath = BP . "/pub/otica_gmc_product_{$websiteName}.tsv";

            /** Selected products */
            $collection = $this->filter->getCollection(
                $this->collectionFactory->create()
            );

            $collection->addAttributeToSelect('*')
                ->addWebsiteFilter($websiteId)
                ->joinField(
                    'qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )
                ->addAttributeToSelect(['stock_status'])
                ->addAttributeToSort('entity_id', 'DESC')
                ->addAttributeToFilter(
                    'status',
                    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                )
                ->load();


            /** Load existing TSV */
            $existingRows = $this->helper->loadExistingTsv($filePath);

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

            $this->messageManager->addSuccessMessage(
                __('%1 products exported/updated for website "%2".', count($collection), $website->getName())
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
        }

        return $this->_redirect('catalog/product/index');
    }
}
