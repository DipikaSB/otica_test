<?php

namespace Setblue\Warranty\Model\ResourceModel;

class Warranty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('Sns_Warranty', 'id');
    }
}
