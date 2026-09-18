<?php

namespace Setblue\GST\Block\Adminhtml\Invoice;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;

class Gst extends Template
{
    protected $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getInvoice()
    {
        return $this->registry->registry('current_invoice');
    }

    public function getGstNumber()
    {
        return $this->getInvoice()->getData('gst_number');
    }
}
