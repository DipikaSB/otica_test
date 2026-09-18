<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Event implements OptionSourceInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_groupCollectionFactory;

    /**
     * Event constructor.
     * @param CollectionFactory $groupCollectionFactory
     */
    public function __construct(
        CollectionFactory $groupCollectionFactory
    ) {
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'add_payment_info',
                'label' => __('add_payment_info')
            ],
            [
                'value' => 'add_shipping_info',
                'label' => __('add_shipping_info')
            ],
            [
                'value' => 'add_to_cart',
                'label' => __('add_to_cart')
            ],
            [
                'value' => 'add_to_wishlist',
                'label' => __('add_to_wishlist')
            ],
            [
                'value' => 'add_to_compare',
                'label' => __('add_to_compare')
            ],
            [
                'value' => 'begin_checkout',
                'label' => __('begin_checkout')
            ],
            [
                'value' => 'purchase',
                'label' => __('purchase')
            ],
            [
                'value' => 'remove_from_cart',
                'label' => __('remove_from_cart')
            ],
            [
                'value' => 'select_item',
                'label' => __('select_item')
            ],
            [
                'value' => 'view_cart',
                'label' => __('view_cart')
            ],
            [
                'value' => 'view_item',
                'label' => __('view_item')
            ],
            [
                'value' => 'view_item_list',
                'label' => __('view_item_list')
            ],
            [
                'value' => 'refund',
                'label' => __('refund')
            ],
            [
                'value' => 'view_promotion',
                'label' => __('view_promotion')
            ],
            [
                'value' => 'select_promotion',
                'label' => __('select_promotion')
            ]
        ];
    }
}
