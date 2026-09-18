<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\TagManager;

class ViewItem extends Manager
{
    /**
     * GetClickRelatedItemData
     */
    public function getClickRelatedItemData()
    {
        $catalogRelBlock = $this->_layout->getBlock('catalog.product.related');
        $relatedItemCollection = '';

        if (empty($catalogRelBlock)) {
            return [];
        }

        $catalogRelBlock->toHtml();

        $blockType = $catalogRelBlock->getData('type');
        if ($blockType == 'related-rule') {
            $relatedItemCollection = $catalogRelBlock->getAllItems();
        } else {
            $relatedItemCollection = $catalogRelBlock->getItems();
        }

        if ($relatedItemCollection===null) {
            return [];
        }

        return $relatedItemCollection;
    }

    /**
     * GetClickUpsellItemData
     */
    public function getClickUpsellItemData()
    {
        $catalogUpsellBlock = $this->_layout->getBlock('product.info.upsell');
        $upselItemCollection = '';

        if (empty($catalogUpsellBlock)) {
            return [];
        }

        $catalogUpsellBlock->toHtml();

        $blockType = $catalogUpsellBlock->getData('type');
        if ($blockType == 'upsell-rule') {
            $upselItemCollection = $catalogUpsellBlock->getAllItems();
        } else {
            $upselItemCollection = $catalogUpsellBlock->getItemCollection()->getItems();
        }

        if ($upselItemCollection===null) {
            return [];
        }

        return $upselItemCollection;
    }

    /**
     * Get Discount Amount.
     *
     * @param mixed $product
     * @return int|string
     */
    public function getDiscountAmount($product)
    {
        $discount = 0;
        if ($product->getTypeId() == 'bundle') {
            $originalPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
            $minimalFinalPriceAmt = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
            if ($originalPrice > $minimalFinalPriceAmt) {
                $discount = $originalPrice - $minimalFinalPriceAmt;
                $discount = number_format($discount, 2, '.', '');
            }
        } else {
            $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
            $originalPrice = $this->helper->getFormatedPrice($product->getPrice());
            if ($originalPrice > $finalPrice) {
                $discount = $originalPrice - $finalPrice;
                $discount = number_format($discount, 2, '.', '');
            }
        }
        return $discount;
    }
}
