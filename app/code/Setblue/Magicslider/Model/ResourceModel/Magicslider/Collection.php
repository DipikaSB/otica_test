<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Model\ResourceModel\Magicslider;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Setblue\Magicslider\Model\Magicslider', 'Setblue\Magicslider\Model\ResourceModel\Magicslider');
    }
}
