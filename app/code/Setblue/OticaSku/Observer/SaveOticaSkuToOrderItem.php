<?php

namespace Setblue\OticaSku\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SaveOticaSkuToOrderItem implements ObserverInterface
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        foreach ($order->getAllItems() as $orderItem) {

            try {
                $product = $this->productRepository->getById($orderItem->getProductId());

                $oticaSku = $product->getCustomAttribute('otica_sku');

                if ($oticaSku) {
                    $orderItem->setData('otica_sku', $oticaSku->getValue());
                    $orderItem->save();
                }

            } catch (\Exception $e) {
                // optional: log error
            }
        }
    }
}
