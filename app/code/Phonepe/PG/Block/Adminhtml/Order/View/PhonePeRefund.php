<?php
namespace Phonepe\PG\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Phonepe\PG\Helper\Data;
use Psr\Log\LoggerInterface;

class PhonePeRefund extends Template
{
    protected $registry;
    protected $logger;
    protected $helper;

    public function __construct(
        Context $context,
        Registry $registry,
        LoggerInterface $logger,
        Data $helper,
    ) {
        $this->registry = $registry;
        $this->logger   = $logger;
        $this->helper   = $helper;
        parent::__construct($context);
    }

    public function getOrder(): ?Order
    {
        return $this->registry->registry('current_order');
    }

    protected function _prepareLayout()
    {
        $order = $this->getOrder();
        if (! $order) {
            $this->logger->info('[PhonePe Refund] Block loaded, but could not get order object.');
            return parent::_prepareLayout();
        }

        $payment           = $order->getPayment();
        $dashboardBaseUrl  = $this->helper->getDashboardUrl();
        $paymentMethodCode = 'phonepe_pg';
        $phonepeOrderId    = $payment->getAdditionalInformation('phonepe_order_id');

        if ($payment->getMethod() === $paymentMethodCode && ! empty($dashboardBaseUrl) && ! empty($phonepeOrderId)) {

            $finalUrl = rtrim($dashboardBaseUrl, '/') . '/' . $phonepeOrderId;
            $this->logger->info('[PhonePe Refund] Final URL: ' . $finalUrl);

            $this->getToolbar()->addChild(
                'phonepe_refund_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label'      => __('Refund on PhonePe'),
                    'class'      => 'action-secondary',
                    'onclick'    => "window.open('" . $finalUrl . "', '_blank')",
                    'sort_order' => 25,
                ]
            );
        } else {
            $this->logger->info('[PhonePe Refund] Pending payment or Dashboard URL not found.');
        }

        return parent::_prepareLayout();
    }
}
