<?php
namespace Setblue\Core\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class ShippingPrice extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * ShippingPrice constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get the shipping price for the checkout page
     *
     * @return string
     */
    public function getShippingPrice()
    {
        $val = $this->scopeConfig->getValue(
            'carriers/flatrate/price',
            ScopeInterface::SCOPE_STORE
        );
        return $this->priceHelper->currency($val, true, false);
    }

    public function getPaymentdisable(){
         $val = $this->scopeConfig->getValue(
            'amasty_checkout/sb_custom_group/payment_sec',
            ScopeInterface::SCOPE_STORE
        );

        return  $val;
    }
}
