<?php
namespace Phonepe\PG\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderStatusCheck extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('phonepe_order_status_check', 'entity_id');
    }
}
