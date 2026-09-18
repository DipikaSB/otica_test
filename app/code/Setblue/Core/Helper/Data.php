<?php

namespace Setblue\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;

class Data extends AbstractHelper
{
    protected $imageHelper;

    protected $productRepository;

    protected $orderRepository;
    protected $shipmentRepository;
    protected $searchCriteriaBuilder;
    protected $carrierFactory;
    private $storeConfig;
    private $currencyCode;

    /**
     * Currency constructor.
     *
     * @param StoreManagerInterface $storeConfig
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Context $context,
        ImageHelper $imageHelper,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CarrierFactory $carrierFactory,
        StoreManagerInterface $storeConfig,
        CurrencyFactory $currencyFactory

    ) {
        parent::__construct($context);
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->carrierFactory = $carrierFactory;
        $this->storeConfig = $storeConfig;
        $this->currencyCode = $currencyFactory->create();
    }
    /**
     * Safely calculate cart item discount %
     */
    public function getCartItemDiscount($regularPrice, $finalPrice)
    {
        // If any price missing OR invalid
        if (empty($regularPrice) || empty($finalPrice) || $regularPrice <= 0) {
            return 0;
        }

        // No discount
        if ($finalPrice >= $regularPrice) {
            return 0;
        }

        // Valid discount
        $discount = $regularPrice - $finalPrice;
        return round(($discount / $regularPrice) * 100);
    }
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku, false, null, true);
    }
    /**
     * Get product image URL
    */
    public function getProductImageUrl($product, $imageId = 'category_page_grid')
    {
        return $this->imageHelper->init($product, $imageId)->getUrl();
    }
    public function getCurrencyName()
    {
        $currentCurrency = $this->storeConfig->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currentCurrency;
    }
    public function getTrackingByOrderIncrementId($incrementId)
    {
        $trackingUrls = [];

        // Get order by increment_id
        $orderCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();

        $orders = $this->orderRepository->getList($orderCriteria)->getItems();

        if (empty($orders)) {
            return $trackingUrls;
        }

        $order = reset($orders);

        // Get shipments
        $shipmentCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $order->getEntityId())
            ->create();

        $shipments = $this->shipmentRepository
            ->getList($shipmentCriteria)
            ->getItems();

        foreach ($shipments as $shipment) {
            foreach ($shipment->getTracks() as $track) {
                if ($track->getTrackNumber() && $track->getLink()) {
                    $trackingUrls[] = str_replace(
                        '%s',
                        $track->getTrackNumber(),
                        $track->getLink()
                    );
                }
            }
        }

        return $trackingUrls;
    }
}
