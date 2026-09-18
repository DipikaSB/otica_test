<?php

namespace Setblue\Warranty\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @category Setblue
 * @package  Setblue_Warranty
 * @author   Setblue 
 * @license  https://www.setblue.com  Open Software License (OSL 3.0)
 * @link     https://www.setblue.com
 */
class Data extends AbstractHelper
{
    /**
     * @var Session
     */
    private $_customerSession;

    /**
     * ShareCart Module XAL path
     *
     * @var XML_PATH_SHARECART_MODULE
     */
    const XML_PATH_SHARECART_MODULE = 'warranty/';

    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Get Config Value
     *
     * @param string $field Field
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get General Config
     *
     * @param string $code Code
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_PATH_SHARECART_MODULE . 'general/' . $code,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        if ($this->getGeneralConfig('enabled') == 1) {
            if (!in_array($this->getCustomerGroup(), $this->getCustomerGroupFromSystemConfig())) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
