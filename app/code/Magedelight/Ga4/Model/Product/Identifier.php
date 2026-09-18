<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model\Product;

use Magento\Framework\Data\OptionSourceInterface;

class Identifier implements OptionSourceInterface
{
    /**
     * OptionArray
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'sku',
                'label' => __('SKU')
            ],
            [
                'value' => 'id',
                'label' => __('ID')
            ]
        ];
    }
}
