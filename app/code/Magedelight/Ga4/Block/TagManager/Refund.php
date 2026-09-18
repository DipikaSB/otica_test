<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\TagManager;

use Magedelight\Ga4\Model\Config\Source\ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\Ga4\Model\DataSaving;
use Magedelight\Ga4\Helper\Data;
use Magedelight\Ga4\Model\CookieData;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Refund extends \Magedelight\Ga4\Block\TagManager\Manager
{
    /**
     * @var \Magedelight\Ga4\Model\DataSaving
     */
    protected $datasaving;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Ga4\Model\CookieData
     */
    protected $storedData;

    /**
     * @var $orderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * Refund constructor.
     * @param Context $context
     * @param DataSaving $datasaving
     * @param Data $helper
     * @param CookieData $storedData
     * @param CollectionFactory $orderCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataSaving $datasaving,
        Data $helper,
        CookieData $storedData,
        CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->_productloader = $_productloader;
        parent::__construct($context, $datasaving, $helper, $storedData, $orderCollectionFactory, $data);
    }

    /**
     * Get Purchase Items.
     *
     * @param mixed $creditmemo
     * @return array
     */
    public function getPurchaseItems($creditmemo)
    {
        $products = [];
        $type = $this->helper->getProductTypeForCheckout();
        $code = $this->helper->getCurrencyCode();
        foreach ($creditmemo->getItems() as $value) {
            $originProduct = $this->getLoadProduct($value->getProductId());
            $productCollection = $originProduct;
            if ($type == ProductType::CHILD) {
                if ($value->getProductType() == Configurable::TYPE_CODE) {
                    $getitems = $value->getChildrenItems();
                    foreach ($getitems as $secondLevelItem) {
                        $productCollection = $secondLevelItem->getProduct();
                    }
                }
            }

            $itemsInfo = [];
            $itemsInfo['currency'] = $code;
            $itemsInfo['item_id'] = $this->helper->getProductIdentifier($productCollection);
            $itemsInfo['item_name'] = $value->getName();
            $itemsInfo['price'] = $this->getPriceBasedUponConfig($value);
            if ($this->helper->isBrandEnabled()) {
                $itemsInfo['item_brand'] = $this->helper->getBrandData($originProduct);
            }
            $categoryName = $this->helper->getCatIds($productCollection->getCategoryIds());
            $itemsInfo['item_category'] = $categoryName;
            if ($this->helper->isVariantEnabled()) {
                $option = $value->getData('product_options');
                $ptype = $value->getData('product_type');
                $variant = $this->helper->getProductOption($option, $ptype);
                if ($variant) {
                    $itemsInfo['item_variant'] = $variant;
                }
            }
            $catIdsOfProducts = $productCollection->getCategoryIds();
            $itemsInfo['item_list_name'] = $categoryName;
            $itemsInfo['item_list_id'] = count($catIdsOfProducts) ? $catIdsOfProducts[0] : '';
            $itemsInfo['quantity'] = $value->getQty();
            $products[] = $itemsInfo;
        }

        return $products;
    }

    /**
     * Get Purchase Amount.
     *
     * @param mixed $order
     * @return mixed
     */
    public function getPurchaseAmount($order)
    {
        $isAllowOrderTotalCalculation = $this->helper->getIsAllowOrderSummary();
        switch ($isAllowOrderTotalCalculation) {
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_SUBTOTAL:
                $orderGrandTotal = $this->getOrderSubTotal($order);
                break;
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_TOTAL:
            default:
                $orderGrandTotal = $this->getOrderGrandTotal($order);
                break;
        }
        return $orderGrandTotal;
    }

    /**
     * Get Purchase Items Ids.
     *
     * @return array
     */
    public function getPurchaseItemsIds()
    {
        $order = $this->getOrder();
        $products = [];
        $type = $this->helper->getProductTypeForCheckout();

        foreach ($order->getAllVisibleItems() as $value) {
            $product = $value->getProduct();
            if ($type == ProductType::CHILD) {
                if ($value->getProductType() == Configurable::TYPE_CODE) {
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
     * Get Order Total Count.
     *
     * @param mixed $order
     * @return int
     */
    public function getOrderTotalCount($order)
    {
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return 1;
        }

        $collection = $this->orderCollectionFactory->create($customerId);
        return $collection->count();
    }

    /**
     * Get All Total Count.
     *
     * @param mixed $order
     * @return float|int
     */
    public function getAllTotalCount($order)
    {
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
     * Get Order Subtotal.
     *
     * @param mixed $order
     * @return mixed
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
     * Get Order Grand Total.
     *
     * @param mixed $order
     * @return mixed
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
     * Get Price Based Upon Config.
     *
     * @param mixed $value
     * @return string
     */
    public function getPriceBasedUponConfig($value)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            if ($this->helper->getSummaryExcludeTax() == 1) {
                $price -= $value->getBaseTaxAmount();
            } else {
                $price = $value->getBasePriceInclTax();
            }
        }

        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $price = $value->getPrice();
            if ($this->helper->getSummaryExcludeTax() == 1) {
                $price -= $value->getTaxAmount();
            } else {
                $price = $value->getPriceInclTax();
            }
        }
        return number_format($price, 2, '.', '');
    }

    /**
     * Get Load Product.
     *
     * @param int|string $id
     * @return \Magento\Catalog\Model\Product
     */
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
}
