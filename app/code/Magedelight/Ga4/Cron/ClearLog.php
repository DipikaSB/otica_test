<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Cron;

use Magedelight\Ga4\Model\GtagFactory;
use Magedelight\Ga4\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

class ClearLog
{
    /**
     * @var GtagFactory
     */
    private $gtag;

    /**
     * @var HelperData
     */
    private $datahelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HelperData $dataHelper
     * @param GtagFactory $gtag
     * @param LoggerInterface $logger
     */
    public function __construct(
        HelperData $dataHelper,
        GtagFactory $gtag,
        LoggerInterface $logger
    ) {
        $this->gtag = $gtag;
        $this->datahelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * Cron request for clear log.
     *
     * @return $this
     */
    public function execute()
    {

        if ($this->datahelper->getCronStatus()) {
            $date = date('Y-m-d'); //today date

            $weekOfdays = [];
            $cronTime = $this->datahelper->getCronTime();
            for ($i = 1; $i <= $cronTime; $i++) {
                $date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
                $weekOfdays = date('Y-m-d', strtotime($date));
            }

            $gtag  = $this->gtag->create()->getCollection()->addFieldToFilter('created_at', ['lt' => $weekOfdays]);
            try {
                $gtag->walk('delete');
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }
        }
        return $this;
    }
}
