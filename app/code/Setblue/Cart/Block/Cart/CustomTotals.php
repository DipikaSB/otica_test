<?php
namespace Setblue\Cart\Block\Cart;

class CustomTotals extends \Magento\Framework\View\Element\Template
{
    protected $checkoutSession;
    protected $pricingHelper;
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;
    protected $currencyFactory;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->currencyFactory = $currencyFactory;
        parent::__construct($context, $data);
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function formatPrice($amount)
    {
        return $this->pricingHelper->currency($amount, true, false);
    }

    public function formatCurrency($amount, $currencyCode)
    {
        $currency = $this->currencyFactory->create()->load($currencyCode);
        return $currency->formatTxt($amount);
    }

    /**
     * @return bool
     */
    public function isPossibleOnepageCheckout()
    {
        return $this->_checkoutHelper->canOnepageCheckout();
    }
    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->checkoutSession->getQuote()->validateMinimumAmount();
    }
}

