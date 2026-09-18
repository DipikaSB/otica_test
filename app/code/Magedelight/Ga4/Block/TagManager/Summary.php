<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\TagManager;

use Magedelight\Ga4\Model\Config\Source\ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Summary extends Manager
{
    /**
     * GetPurchaseAmount
     */
    public function getPurchaseAmount()
    {
        $isAllowOrderTotalCalculation = $this->helper->getIsAllowOrderSummary();
        $order =  $this->getOrder();
        switch ($isAllowOrderTotalCalculation) {
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_SUBTOTAL:
                $orderGrandTotal = $this->getOrderSubTotal($order);
                break;
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_TOTAL:
            default:
                $orderGrandTotal = $this->getOrderGrandTotal($order);
                break;
        }
        //$orderGrandTotal = number_format($orderGrandTotal, 2, '.', '');
        return (float)$orderGrandTotal;
    }

    /**
     * GetPurchaseItemsIds
     */
    public function getPurchaseItemsIds()
    {
        $order = $this->getOrder();
        $products = [];
        $type = $this->helper->getProductTypeForCheckout();

        foreach ($order->getAllVisibleItems() as $value) {
            $product = $value->getProduct();
            if ($type == ProductType::CHILD) {
                if ($value->getProductType()== Configurable::TYPE_CODE) {
                    $getitems = $value->getChildrenItems();
                    foreach ($getitems as $secondLevelItem) {
                        $productCollection = $secondLevelItem->getProduct();
                    }
                }
            }
            $products[] = $this->helper->getProductIdentifier($productCollection);
        }

        return $products;
    }

    /**
     * GetOrderTotalCount
     */
    public function getOrderTotalCount()
    {
        $order =  $this->getOrder();
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return 1;
        }

        $collection = $this->orderCollectionFactory->create($customerId);
        return $collection->count();
    }

    /**
     * GetAllTotalCount
     */
    public function getAllTotalCount()
    {
        $order =  $this->getOrder();
        $customerId = $order->getCustomerId();
        $currencyOption = $this->helper->getCurrencyOption();
        if (!$customerId) {
            if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
                $orderValue = $order->getBaseGrandtotal();
            }
            if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
                $orderValue = $order->getGrandtotal();
            }
            return $orderValue;
        }

        $orderTotals = $this->orderCollectionFactory->create($customerId)
            ->addFieldToSelect('*');

        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $grandTotals = $orderTotals->getColumnValues('base_grand_total');
            $refundTotals = $orderTotals->getColumnValues('base_total_refunded');
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $grandTotals = $orderTotals->getColumnValues('grand_total');
            $refundTotals = $orderTotals->getColumnValues('total_refunded');
        }
        return array_sum($grandTotals) - array_sum($refundTotals);
    }

    /**
     * GetOrderSubTotal
     *
     * @param Order $order
     */
    public function getOrderSubTotal($order)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $orderTotal = $order->getBaseSubtotal();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $orderTotal = $order->getSubtotal();
        }
        return $orderTotal;
    }

    /**
     * GetOrderGrandTotal
     *
     * @param Order $order
     */
    public function getOrderGrandTotal($order)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $orderTotal = $order->getBaseGrandtotal();
            if ($this->helper->getSummaryExcludeTax()) {
                $orderTotal -= $order->getBaseTaxAmount();
            }

            if ($this->helper->getSummaryExcludeShipping()) {
                $orderTotal -= $order->getBaseShippingAmount();
            }
        }

        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $orderTotal = $order->getGrandtotal();
            if ($this->helper->getSummaryExcludeTax()) {
                $orderTotal -= $order->getTaxAmount();
            }

            if ($this->helper->getSummaryExcludeShipping()) {
                $orderTotal -= $order->getShippingAmount();
            }
        }
        return $orderTotal;
    }

    /**
     * GetDataHelper
     */
    public function getDataHelper()
    {
        return $this->helper;
    }

    /**
     * Get Order Tax.
     *
     * @param mixed $order
     * @return mixed
     */
    public function getOrderTax($order)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return $order->getBaseTaxAmount();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $order->getTaxAmount();
        }
    }

    /**
     * Get Order Shipping
     *
     * @param mixed $order
     * @return mixed
     */
    public function getOrderShipping($order)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return $order->getBaseShippingAmount();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $order->getShippingAmount();
        }
    }
}
