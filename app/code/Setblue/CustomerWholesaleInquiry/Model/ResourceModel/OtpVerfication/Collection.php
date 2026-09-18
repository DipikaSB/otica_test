<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Model\ResourceModel\OtpVerfication;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'otp_id';
    
    protected function _construct()
    {
        $this->_init(
            'Setblue\CustomerWholesaleInquiry\Model\OtpVerfication',
            'Setblue\CustomerWholesaleInquiry\Model\ResourceModel\OtpVerfication'
        );
    }
}
