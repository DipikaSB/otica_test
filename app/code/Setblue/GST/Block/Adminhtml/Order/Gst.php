<?php

namespace Setblue\GST\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;

class Gst extends Template
{
    protected $registry;
    protected $orderRepository;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    public function getGstNumber()
    {
        // For Invoice Create Page
        $invoice = $this->registry->registry('current_invoice');

        if ($invoice && $invoice->getOrderId()) {
            $order = $this->orderRepository->get($invoice->getOrderId());
            return $order->getData('gst_number');
        }

        // Fallback: Order View Page
        $order = $this->registry->registry('current_order');

        if ($order && $order->getId()) {
            return $order->getData('gst_number');
        }

        return null;
    }
}
