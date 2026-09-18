<?php
namespace SEO\GMC\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use SEO\GMC\Helper\GmcExportHelper;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class ExportAllCommand extends Command
{
    protected CollectionFactory $collectionFactory;
    protected WebsiteRepositoryInterface $websiteRepository;
    protected GmcExportHelper $helper;
    protected State $appState;
    protected Configurable $configurableResource;

    public function __construct(
        CollectionFactory $collectionFactory,
        WebsiteRepositoryInterface $websiteRepository,
        GmcExportHelper $helper,
        Configurable $configurableResource,
        State $appState = null
    ) {
        parent::__construct();

        $this->collectionFactory = $collectionFactory;
        $this->websiteRepository = $websiteRepository;
        $this->helper = $helper;
        $this->configurableResource = $configurableResource;

        // Fallback for stale DI
        $this->appState = $appState
            ?: ObjectManager::getInstance()->get(State::class);
    }

    protected function configure()
    {
        $this->setName('seo:gmc:export');
        $this->setDescription('Export Google Merchant Center TSV for all websites (one file per website)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            // area already set
        }

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        try {
            $websites = $this->websiteRepository->getList();
            $totalWebsites = 0;
            $grandTotalProducts = 0;

            foreach ($websites as $website) {
                $websiteId   = (int) $website->getId();
                $websiteName = $website->getName();

                //  SANITIZE WEBSITE NAME FOR FILE
                $safeWebsiteName = strtolower($websiteName);

                if ($safeWebsiteName === 'admin') { 
                    continue; 
                }

                $safeWebsiteName = preg_replace('/[^a-z0-9]+/', '_', $safeWebsiteName);
                $safeWebsiteName = trim($safeWebsiteName, '_');

                $filePath = BP . "/pub/otica_gmc_product_{$safeWebsiteName}.tsv";
                $existingRows = [];

                $output->writeln("<info>Exporting website: {$websiteName}</info>");
                $output->writeln("<comment>File: {$filePath}</comment>");

                $collection = $this->collectionFactory->create();
                $collection->addWebsiteFilter($websiteId);

                $collection->addAttributeToSelect([
                    'sku',
                    'name',
                    'price',
                    'special_price',
                    'status',
                    'type_id'
                ]);

                $collection
                    ->joinField(
                        'qty',
                        'cataloginventory_stock_item',
                        'qty',
                        'product_id=entity_id',
                        '{{table}}.stock_id=1',
                        'left'
                    )
                    ->addAttributeToFilter(
                        'status',
                        \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                    )
                    ->addAttributeToSort('entity_id', 'DESC');

                $collection->setPageSize(200);
                $pages = $collection->getLastPageNumber();
                $websiteProductCount = 0;

                for ($page = 1; $page <= $pages; $page++) {
                    $collection->setCurPage($page);
                    $collection->load();

                    foreach ($collection as $product) {

                        $parentIds = $this->configurableResource
                            ->getParentIdsByChild((int) $product->getId());

                        foreach ($this->helper->buildRow($product, $websiteId) as $row) {
                            if (!empty($row[0])) {
                                $existingRows[$row[0]] = $row;
                                $websiteProductCount++;
                            }
                        }
                    }

                    $collection->clear();
                    $output->writeln("Processed page {$page}/{$pages}");
                }

                // WRITE FILE FOR THIS WEBSITE
                $this->helper->writeTsv($filePath, $existingRows);

                $output->writeln("<info>Saved: {$filePath}</info>");
                $output->writeln(
                    "<comment>Website done. Products: {$websiteProductCount}</comment>"
                );

                $grandTotalProducts += $websiteProductCount;
                $totalWebsites++;
            }

            $output->writeln(
                "<info>DONE. Websites: {$totalWebsites}, Total Products: {$grandTotalProducts}</info>"
            );

            return Cli::RETURN_SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        }
    }
}