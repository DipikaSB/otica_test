<?php

namespace Setblue\Warranty\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;

class Warranty extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Setblue\Warranty\Model\ResourceModel\Warranty');
        $cacheManager = ObjectManager::getInstance()->get('\Magento\Framework\App\Cache\Manager');
        $types = ['config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice'];
        $cacheManager->flush($types);
    }
}
