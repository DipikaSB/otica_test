<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Model\ResourceModel;

class Magicslider extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('setblue_magicslider', 'magicslider_id');
    }
}
