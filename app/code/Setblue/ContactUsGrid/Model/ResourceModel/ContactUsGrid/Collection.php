<?php
namespace Setblue\ContactUsGrid\Model\ResourceModel\ContactUsGrid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Setblue\ContactUsGrid\Model\ContactUsGrid as Model;
use Setblue\ContactUsGrid\Model\ResourceModel\ContactUsGrid as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    /**
     * Define collection model and resource model classes.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
