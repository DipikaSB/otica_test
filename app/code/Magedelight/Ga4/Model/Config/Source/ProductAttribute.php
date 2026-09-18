<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttribute implements OptionSourceInterface
{

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * ProductAttribute constructor.
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $arr = [];

        $attributesCollection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('used_in_product_listing', 1);
        foreach ($attributesCollection as $attribute) {
            $arr[] = [
                'value' => $attribute->getData('attribute_code'),
                'label' => $attribute->getData('frontend_label')
            ];
        }
        return $arr;
    }
}
