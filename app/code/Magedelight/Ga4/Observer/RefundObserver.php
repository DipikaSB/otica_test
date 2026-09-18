<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Observer;

use Magedelight\Ga4\Helper\Data;
use Magedelight\Ga4\Model\Config\Source\ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magedelight\Ga4\Model\EventTrigger;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProduct;

class RefundObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $mdhelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $adminSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigurableProduct
     */
    protected $configurableProduct;

    /**
     * RefundObserver constructor.
     * @param Data $mdhelper
     * @param EventTrigger $eventTrigger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Backend\Model\Session $adminSession
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Psr\Log\LoggerInterface $logger
     * @param ConfigurableProduct $configurableProduct
     */
    public function __construct(
        Data $mdhelper,
        EventTrigger $eventTrigger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\Session $adminSession,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Psr\Log\LoggerInterface $logger,
        ConfigurableProduct $configurableProduct
    ) {
        $this->mdhelper = $mdhelper;
        $this->eventTrigger = $eventTrigger;
        $this->orderRepository = $orderRepository;
        $this->adminSession = $adminSession;
        $this->_productloader = $_productloader;
        $this->logger = $logger;
        $this->configurableProduct = $configurableProduct;
    }

    /**
     * Refund Observer.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
       /* @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        try {
            if ($this->mdhelper->isGTMStatus() && $this->mdhelper->GTMEventConfigured('refund')) {
                $items = [];
                $creditmemo = $observer->getEvent()->getCreditmemo();
                $order = $this->orderRepository->get($creditmemo->getOrderId());
                $items = $this->getPurchaseItems($creditmemo);
                $data = [
                    'event' => 'refund',
                    'ecommerce' => [
                        'currency' => $order->getOrderCurrencyCode(),
                        'transaction_id' =>  $order->getIncrementId(),
                        'value' => $this->getPurchaseAmount($order),
                        'coupon' => ($order->getCouponCode()) ? (string)$order->getCouponCode():null,
                        'shipping' => (float)$creditmemo->getShippingAmount(),
                        'tax' => (float)$creditmemo->getTaxAmount(),
                        'items' => $items
                    ]
                ];

                if ($data) {
                    $this->adminSession->setRefundData($data);

                    $this->mdhelper->setTagManagerReportData($data);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get Purchase Items.
     *
     * @param mixed $creditmemo
     * @return array
     */
    private function getPurchaseItems($creditmemo)
    {
        $products = [];
        $type = $this->mdhelper->getProductTypeForCheckout();
        $k = 0;

        foreach ($creditmemo->getAllItems() as $value) {

            $originProduct = $this->getLoadProduct($value->getProductId());
            $productCollection = $originProduct;

            if ($type == ProductType::CHILD) {
                if ($productCollection->getTypeId() == Configurable::TYPE_CODE) {
                    continue;
                }
            }

            if ($type == ProductType::PARENT) {
                $product = $this->configurableProduct->getParentIdsByChild($productCollection->getEntityId());
                if (isset($product[0])) {
                    continue;
                }
            }

            $itemsInfo = [];
            $itemsInfo['item_id'] = $this->mdhelper->getProductIdentifier($productCollection);
            $itemsInfo['item_name'] = $productCollection->getName();
            $itemsInfo['affiliation'] = $this->mdhelper->getNameOfAffilation();
            $itemsInfo['index'] = $k;
            if ($this->mdhelper->isBrandEnabled()) {
                $itemsInfo['item_brand'] = $this->mdhelper->getBrandData($productCollection);
            }
            $categoryName = $this->mdhelper->getCatIds($productCollection->getCategoryIds());
            if (!empty($categoryName)) {
                $j = 1;
                $countCategoryName = count($categoryName);
                for ($i = 0; $i < $countCategoryName; $i++) {
                    if ($j > 1) {
                        $itemsInfo['item_category'.$i] = $categoryName[$i];
                    } else {
                        $itemsInfo['item_category'] = $categoryName[$i];
                    }
                    $j++;
                }
            }

            $catIdsOfProducts = $productCollection->getCategoryIds();
            if (!empty($categoryName)) {
                $itemsInfo['item_list_name'] = implode('/', $categoryName);
            }
            $itemsInfo['item_list_id'] = count($catIdsOfProducts) ? $catIdsOfProducts[0] : '';
            if ($this->mdhelper->isVariantEnabled()) {
                $option = $value->getData('product_options');
                $ptype = $value->getData('product_type');
                $variant = $this->mdhelper->getProductOption($option, $ptype);
                if ($variant) {
                    $productDetail['item_variant'] = $variant;
                }
            }
            //$itemsInfo['price'] = number_format($value->getPrice(), 2, '.', '');
            $itemsInfo['quantity'] = $value->getQty();

            $customAttribute = $this->mdhelper->getProductCustomAttribute($originProduct);
            if (!empty($customAttribute)) {
                foreach ($customAttribute as $key => $value) {
                    $itemsInfo[$key] = $value;
                }
            }

            $products[] = $itemsInfo;
            $k++;
        }
        return $products;
    }

    /**
     * Get Load Product.
     *
     * @param int|string $id
     * @return \Magento\Catalog\Model\Product
     */
    private function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    /**
     * Get Purchase Amount.
     *
     * @param mixed $order
     * @return string
     */
    private function getPurchaseAmount($order)
    {
        $isAllowOrderTotalCalculation = $this->mdhelper->getIsAllowOrderSummary();
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
     * Get Order Grand Total.
     *
     * @param mixed $order
     * @return mixed
     */
    public function getOrderGrandTotal($order)
    {
        $orderTotal = $order->getGrandtotal();

        if ($this->mdhelper->getSummaryExcludeTax()) {
            $orderTotal -= $order->getTaxAmount();
        }

        if ($this->mdhelper->getSummaryExcludeShipping()) {
            $orderTotal -= $order->getShippingAmount();
        }

        return $orderTotal;
    }

    /**
     * Get Order SubTotal.
     *
     * @param mixed $order
     * @return mixed
     */
    public function getOrderSubTotal($order)
    {
        return $order->getSubtotal();
    }
}
