<?php

namespace Setblue\Warranty\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
 
class Inquiryform extends Template
{
    protected $directoryBlock;
    protected $_isScopePrivate;
    protected $customerSession;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Block\Data $directoryBlock,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->directoryBlock = $directoryBlock;
        $this->customerSession = $customerSession;
    }
    
     /** Return customer data for template */
    public function getCustomerData()
    {
        if ($this->customerSession->isLoggedIn()) {
            return [
                'firstname' => $this->customerSession->getCustomer()->getFirstname(),
                'lastname'  => $this->customerSession->getCustomer()->getLastname(),
                'email'     => $this->customerSession->getCustomer()->getEmail()
            ];
        }

        return false;
    }
    public function getforendSave()
    {
        return $this->getUrl('warranty/index/savedataforend', ['_secure' => true]);
    }
}
