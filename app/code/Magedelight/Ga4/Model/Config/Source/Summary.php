<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Summary implements OptionSourceInterface
{
    public const CHECKOUT_SUBTOTAL = 'subtotal';
    public const CHECKOUT_TOTAL = 'grandtotal';

    /**
     * OptionArray
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CHECKOUT_TOTAL,
                'label' => __('Grandtotal')
            ],
            ['value' => self::CHECKOUT_SUBTOTAL,
                'label' => __('Subtotal')
            ]
        ];
    }
}
