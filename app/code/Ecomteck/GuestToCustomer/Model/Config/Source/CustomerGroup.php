<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_GuestToCustomer
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\GuestToCustomer\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Config category source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CustomerGroup implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_customerGroupCollectionFactory;

    /**
     * CustomerGroup constructor.
     * @param CollectionFactory $_customerGroupCollectionFactory
     */
    public function __construct(
        CollectionFactory $_customerGroupCollectionFactory) {
        $this->_customerGroupCollectionFactory = $_customerGroupCollectionFactory;
    }

    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {

        $collection = $this->_customerGroupCollectionFactory->create();
        $options[] = ['label' => __('New Account Default Group'), 'value' => '0'];

        foreach ($collection as $customerGroups) {
            if(0 == $customerGroups->getId())
                continue;
            $options[] = ['label' => $customerGroups->getCustomerGroupCode(), 'value' => $customerGroups->getId()];
        }

        return $options;
    }
}
