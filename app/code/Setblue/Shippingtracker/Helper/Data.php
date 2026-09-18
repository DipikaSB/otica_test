<?php
namespace Setblue\Shippingtracker\Helper;

use Magento\Shipping\Helper\Data as CoreShippingHelper;

class Data extends CoreShippingHelper
{
    /**
     * Get courier tracking URL from configuration
     *
     * @param string $carrierCode
     * @param string $trackingNumber
     * @return string|null
     */
    public function getCourierTrackingUrl($carrierCode, $trackingNumber)
    {
        $urlTemplate = $this->scopeConfig->getValue(
            'courier_tracking/urls/' . $carrierCode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($urlTemplate) {
            if (strpos($urlTemplate, '%s') !== false) {
                return sprintf($urlTemplate, urlencode($trackingNumber));
            } else {
                return str_replace('{TRACKING_NUMBER}', urlencode($trackingNumber), $urlTemplate);
            }
        }

        return null;
    }

    /**
     * Override: Shipping tracking popup URL getter
     *
     * @param \Magento\Sales\Model\AbstractModel $model
     * @return string
     */
    public function getTrackingPopupUrlBySalesModel($model)
    {
        if ($model instanceof \Magento\Sales\Model\Order\Shipment\Track) {
            $carrierCode = $model->getCarrierCode();
            $trackingNumber = $model->getTrackNumber();

            $externalUrl = $this->getCourierTrackingUrl($carrierCode, $trackingNumber);
            if ($externalUrl) {
                return $externalUrl;
            }

            return parent::getTrackingPopupUrlBySalesModel($model);
        }

        return parent::getTrackingPopupUrlBySalesModel($model);
    }
}
