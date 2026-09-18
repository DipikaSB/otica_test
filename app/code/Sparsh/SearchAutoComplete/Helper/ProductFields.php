<?php
/**
 * Class ProductFields
 *
 * PHP version 7 & 8
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SearchAutoComplete\Helper;

/**
 * Class ProductFields
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class ProductFields
{
    /**
     * 
     * @var Options
     */
    protected $options;

    /**
     * Option array for product attribute config
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [
            ['value' => 'name', 'label' => __('Product Name')],
            ['value' => 'sku', 'label' => __('Product SKU')],
            ['value' => 'image', 'label' => __('Product Image')],
            ['value' => 'reviews_rating', 'label' => __('Product Review Rating')],
            ['value' => 'short_description', 'label' => __('Product Short Description')],
            ['value' => 'description', 'label' => __('Product Description')],
            ['value' => 'price', 'label' => __('Product Price')],
            ['value' => 'add_to_cart', 'label' => __('Product Add to Cart Button')]
        ];

        return $this->options;
    }
}
