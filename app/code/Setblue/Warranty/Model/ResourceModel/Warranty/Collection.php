<?php

namespace Setblue\Warranty\Model\ResourceModel\Warranty;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    
    protected function _construct()
    {
        $this->_init(
            'Setblue\Warranty\Model\Warranty',
            'Setblue\Warranty\Model\ResourceModel\Warranty'
        );
    }
}
