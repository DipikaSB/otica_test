<?php
namespace Phonepe\PG\Model;

use Magento\Framework\Model\AbstractModel;

class OrderStatusCheck extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Phonepe\PG\Model\ResourceModel\OrderStatusCheck::class);
    }
}
