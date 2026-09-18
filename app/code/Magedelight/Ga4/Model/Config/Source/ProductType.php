<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductType implements OptionSourceInterface
{
    public const CHILD = 'child';
    public const PARENT = 'parent';

    /**
     * OptionArray
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CHILD,
                'label' => __('Child')
            ],
            [
                'value' => self::PARENT,
                'label' => __('Parent')
            ]
        ];
    }
}
