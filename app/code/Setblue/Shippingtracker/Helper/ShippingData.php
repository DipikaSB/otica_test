<?php
namespace Setblue\Shippingtracker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class ShippingData extends AbstractHelper
{
    const XML_PATH_SHIPPINGTRACKER = 'shippingtracker_section/';

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomCarrierTitle()
    {
        $customCarrier = [];
        $maxCustoms = 15;

        for ($i = 1; $i <= $maxCustoms; $i++) {
            $enablePath = self::XML_PATH_SHIPPINGTRACKER . "custom_shippingtracker_{$i}/custom_shippingtracker_enable_{$i}";
            $titlePath = self::XML_PATH_SHIPPINGTRACKER . "custom_shippingtracker_{$i}/custom_shippingtracker_title_{$i}";

            $enabled = $this->getConfigValue($enablePath);
            $title = $this->getConfigValue($titlePath);

            if ($enabled && $title) {
                $customCarrier["customcarrier{$i}"] = $title;
            }
        }

        return $customCarrier;
    }
}
