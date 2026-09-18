<?php

namespace Setblue\Core\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const XML_PATH_SHIPING_DISABLE = 'amasty_checkout/sb_custom_group/shipping_sec';
    const XML_PATH_PAYMENT_DISABLE = 'amasty_checkout/sb_custom_group/payment_sec';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        $config = [];
        $config['shipping_disable'] = $this->scopeConfig->getValue(
            self::XML_PATH_SHIPING_DISABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $config['payment_disable'] = $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_DISABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $config;
    }
}
