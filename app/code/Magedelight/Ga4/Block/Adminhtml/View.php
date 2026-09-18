<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\Adminhtml;

use Magento\Backend\Block\Template;

class View extends Template
{
    /**
     * @var \Magedelight\Ga4\Model\ResourceModel\Gtag\CollectionFactory
     */
    protected $logCollection;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param \Magedelight\Ga4\Model\ResourceModel\Gtag\CollectionFactory $logCollection
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magedelight\Ga4\Model\ResourceModel\Gtag\CollectionFactory $logCollection
    ) {
        $this->logCollection = $logCollection;
        parent::__construct($context);
    }

    /**
     * Row Data.
     *
     * @return array
     */
    public function rowData()
    {
        $logData = $this->logCollection->create()->addFieldToFilter('entity_id', $this->getRowId());
        $rowDetail = [];
        foreach ($logData->getData() as $key => $rowsData) {
            $rowDetail = [
            'entityId' => $rowsData['entity_id'],
            'pageUrl' => $rowsData['event_label'],
            'eventData' => $rowsData['event_data']
            ];
        }
        return $rowDetail;
    }
}
