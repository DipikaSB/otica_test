<?php
namespace Phonepe\PG\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class CheckStatusButton extends Template
{
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getOrder(): ?Order
    {
        return $this->registry->registry('current_order');
    }

    protected function _prepareLayout()
    {
        $order = $this->getOrder();
        if (! $order) {
            return parent::_prepareLayout();
        }

        $payment = $order->getPayment();

        // Only show the button if the order was paid with PhonePe AND is in the pending payment state.
        if ($payment->getMethod() === 'phonepe_pg' && $order->getState() === Order::STATE_PENDING_PAYMENT) {
            $url = $this->getUrl('phonepe/order/checkstatus', ['order_id' => $order->getId()]);

            $this->getToolbar()->addChild(
                'phonepe_check_status_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label'      => __('Check PhonePe Status'),
                    'class'      => 'action-secondary',
                    'onclick'    => "setLocation('" . $url . "')",
                    'sort_order' => 28,
                ]
            );
        }

        return parent::_prepareLayout();
    }
}
