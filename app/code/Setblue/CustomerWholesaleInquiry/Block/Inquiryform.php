<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Block;
 
class Inquiryform extends \Magento\Framework\View\Element\Template
{
    protected $directoryBlock;
    protected $_isScopePrivate;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Block\Data $directoryBlock,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->directoryBlock = $directoryBlock;
    }
 
    public function getCountries()
    {
        $country = $this->directoryBlock->getCountryHtmlSelect();
        return $country;
    }
    public function getCountryAction()
    {
        return $this->getUrl('wholesale/form/country', ['_secure' => true]);
    }
    public function SentOtp()
    {
        return $this->getUrl('wholesale/index/sendotp', ['_secure' => true]);
    }
    public function ResentOtp()
    {
        return $this->getUrl('wholesale/index/resentotp', ['_secure' => true]);
    }
    public function getVerification()
    {
        return $this->getUrl('wholesale/index/otpverification', ['_secure' => true]);
    }
    public function getforendSave()
    {
        return $this->getUrl('wholesale/index/savedataforend', ['_secure' => true]);
    }
}
