<?php

namespace Setblue\GST\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveGstToInvoice implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order   = $invoice->getOrder();

        if ($order->getData('gst_number')) {
            $invoice->setData('gst_number', $order->getData('gst_number'));
        }
    }
}
