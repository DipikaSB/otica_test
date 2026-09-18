<?php
namespace Phonepe\PG\Model\ResourceModel\OrderStatusCheck;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            \Phonepe\PG\Model\OrderStatusCheck::class,
            \Phonepe\PG\Model\ResourceModel\OrderStatusCheck::class
        );
    }
}
