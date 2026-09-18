<?php

namespace Setblue\GoogleReview\Model\ResourceModel\Review;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(
            \Setblue\GoogleReview\Model\Review::class,
            \Setblue\GoogleReview\Model\ResourceModel\Review::class
        );
    }
}
