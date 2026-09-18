<?php
namespace Setblue\Shippingtracker\Plugin\Block\Adminhtml\Order;

class Tracking
{
    protected $shippingTrackerHelper;

    public function __construct(
        \Setblue\Shippingtracker\Helper\ShippingData $shippingTrackerHelper
    ) {
        $this->shippingTrackerHelper = $shippingTrackerHelper;
    }

    public function afterGetCarriers(
        \Magento\Shipping\Block\Adminhtml\Order\Tracking $subject,
        $result
    ) {
        $customCarrier = $this->shippingTrackerHelper->getCustomCarrierTitle();

        if (!empty($customCarrier)) {
            foreach ($customCarrier as $code => $title) {
                $result[$code] = $title;
            }
        }

        return $result;
    }
}
