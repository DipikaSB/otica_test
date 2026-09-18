<?php

namespace Setblue\GST\Block\Invoice;

use Magento\Framework\View\Element\Template;
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
        $invoice = $this->getInvoice();
        return $invoice ? $invoice->getData('gst_number') : null;
    }
}