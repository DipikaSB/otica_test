<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Gtag extends AbstractDb
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('magedelight_ga4', 'entity_id');
    }
}
