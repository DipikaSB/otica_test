<?php

namespace Meetanshi\Paymulti\Model\Source;

/**
 * Class Currency
 * @package Meetanshi\Paymulti\Model\Source
 */
class Currency extends \Magento\Config\Model\Config\Source\Locale\Currency
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_currencySymbol;
    /**
     * @var \Meetanshi\Paymulti\Helper\Data
     */
    protected $_helper;


    public function __construct(
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Store\Model\StoreManagerInterface $currencySymbol,
        \Meetanshi\Paymulti\Helper\Data $helper
    )
    {
        $this->_localeLists = $localeLists;
        $this->_currencySymbol = $currencySymbol;
        $this->_helper = $helper;
        parent::__construct($localeLists);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray()
    {
        $_supportedCurrencyCodes = $this->_helper->getSupportedCurrency();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create(\Magento\Config\Controller\Adminhtml\System\Config\Edit::class);
        $store = $product->getRequest()->getParam('store');

        if ($store == null) {
            $store = 0;
        }

        /** @var \Magento\Store\Model\Store $currentStore */
//        $currentStore = $this->_currencySymbol->getStore();
//        $_availableCurrencyCodes = $currentStore->getAvailableCurrencyCodes(true);

        $_availableCurrencyCodes = $this->_currencySymbol->getStore($store)->getAvailableCurrencyCodes(true);

        if (!$this->_options) {
            $this->_options = $this->_localeLists->getOptionCurrencies();
        }
        $options = [];
        foreach ($this->_options as $option) {
            if (in_array($option['value'], $_supportedCurrencyCodes) && in_array($option['value'], $_availableCurrencyCodes)) {
                $options[] = $option;
            }
        }
        return $options;
    }
}
