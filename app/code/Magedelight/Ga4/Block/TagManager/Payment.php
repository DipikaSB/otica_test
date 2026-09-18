<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\TagManager;

class Payment extends Manager
{
    /**
     * GetPurchaseAmount
     */
    public function getPurchaseAmount()
    {
        $isAllowOrderTotalCalculation = $this->helper->getIsAllowOrderSummary();
        $quote  = $this->getQuote();
        switch ($isAllowOrderTotalCalculation) {
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_SUBTOTAL:
                $orderGrandTotal = $this->getOrderSubTotal($quote);
                break;
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_TOTAL:
            default:
                $orderGrandTotal = $this->getOrderGrandTotal($quote);
                break;
        }
        return $orderGrandTotal;
    }

    /**
     * GetOrderSubTotal
     *
     * @param mixed $quote
     * @return mixed
     */
    public function getOrderSubTotal($quote)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        $orderTotal = $quote->getBaseSubtotal();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $orderTotal = $quote->getBaseSubtotal();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $orderTotal = $quote->getSubtotal();
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
}
