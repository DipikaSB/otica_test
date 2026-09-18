<?php

namespace Setblue\OrderCancelEmail\Controller\Recovery;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;

class Index extends Action
{
    protected $orderRepository;
    protected $cart;
    protected $productRepository;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        Cart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');

        try {
            $order = $this->orderRepository->get($orderId);

            foreach ($order->getAllVisibleItems() as $item) {

                $product = $this->productRepository->getById(
                    $item->getProductId()
                );

                $buyRequest = $item->getProductOptionByCode(
                    'info_buyRequest'
                );

                $this->cart->addProduct(
                    $product,
                    new DataObject($buyRequest ?: ['qty' => $item->getQtyOrdered()])
                );
            }

            $this->cart->save();

            return $this->_redirect('checkout');

        } catch (\Exception $e) {

            $this->messageManager->addErrorMessage(
                __('Unable to restore your order.')
            );

            return $this->_redirect('');
        }
    }
}