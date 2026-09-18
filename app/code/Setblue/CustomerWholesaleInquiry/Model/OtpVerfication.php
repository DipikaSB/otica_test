<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;

class OtpVerfication extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Setblue\CustomerWholesaleInquiry\Model\ResourceModel\OtpVerfication');
        $cacheManager = ObjectManager::getInstance()->get('\Magento\Framework\App\Cache\Manager');
        $types = ['config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice'];
        $cacheManager->flush($types);
    }
}
