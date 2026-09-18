<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Backend\App\Action\Context;
use Magedelight\Ga4\Model\ResourceModel\Gtag\CollectionFactory;
use Magedelight\Ga4\Helper\Data as HelperData;

class ClearLog extends Action
{
    /**
     * @var string
     */
    public const ACTION_RESOURCE = 'Magedelight_Ga4::gtag';

    /**
     * @var GtagFactory
     */
    private $gtag;

    /**
     * @var HelperData
     */
    protected $datahelper;

    /**
     * @param Context $context
     * @param HelperData $dataHelper
     * @param CollectionFactory $gtag
     */
    public function __construct(
        Context $context,
        HelperData $dataHelper,
        CollectionFactory $gtag
    ) {
        parent::__construct($context);
        $this->gtag = $gtag;
        $this->datahelper = $dataHelper;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ClearLog
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

            $gtag  = $this->gtag->create()->addFieldToFilter('created_at', ['lt' => $weekOfdays]);
            try {
                $gtag->walk('delete');
                $this->messageManager->addSuccessMessage(__('The gtag eveng log has been cleared.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this;
    }
}
