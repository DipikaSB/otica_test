<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Block;

class FooterLink extends \Magento\Framework\View\Element\Html\Link
{
    public function _toHtml()
    {
        if (!$this->_scopeConfig->isSetFlag('CustomerWholesaleInquiry/general/enable') ||
            !$this->_scopeConfig->isSetFlag('CustomerWholesaleInquiry/general/footerlink')
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
