<?php

namespace Setblue\GoogleReview\Model;

use Magento\Framework\Model\AbstractModel;

class Review extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Setblue\GoogleReview\Model\ResourceModel\Review::class);
    }
}
