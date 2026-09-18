<?php

namespace Setblue\InfoOrderGrid\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class StatusGuide extends Template
{
    protected $orderCollectionFactory;

    public function __construct(
        Template\Context $context,
        CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function getStatusCount($status)
    {
        return $this->orderCollectionFactory->create()
            ->addFieldToFilter('status', $status)
            ->getSize();
    }
}