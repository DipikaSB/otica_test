<?php

namespace Setblue\ShippingRestriction\Plugin;

use Closure;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class ShipmentSavePlugin
{
    protected $messageManager;
    protected $resultRedirectFactory;
    protected $orderRepository;

    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderRepository = $orderRepository;
    }

    public function aroundExecute(
        \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save $subject,
        Closure $proceed
    ) {
        $orderId = $subject->getRequest()->getParam('order_id');

        if ($orderId) {
            $order = $this->orderRepository->get($orderId);

            if (!$order->hasInvoices()) {

                $this->messageManager->addErrorMessage(
                    __('Please create invoice before shipment.')
                );

                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath(
                    'sales/order/view',
                    ['order_id' => $orderId]
                );
            }
        }

        return $proceed();
    }
}