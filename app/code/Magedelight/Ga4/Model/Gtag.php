<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model;

use Magento\Framework\Model\AbstractModel;

class Gtag extends AbstractModel
{

    protected const CACHE_TAG = 'entity_id';

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Ga4\Model\ResourceModel\Gtag');
    }

    /**
     * GetIdentities
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
