<?php

namespace Setblue\ContactUsGrid\Model;

use Magento\Framework\Model\AbstractModel;
use Setblue\ContactUsGrid\Model\ResourceModel\ContactUsGrid as ResourceModel;

class ContactUsGrid extends AbstractModel
{
    /**
     * Initialize model and set the resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
