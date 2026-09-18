<?php
namespace Phonepe\PG\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Phonepe\PG\Service\EventService;
use Phonepe\PG\Service\PaymentResponseHandler;
use Phonepe\PG\Service\SdkClientFactory;
use Psr\Log\LoggerInterface;

class CheckStatus extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Sales::view';

    protected $orderRepository;
    protected $sdkClientFactory;
    protected $paymentResponseHandler;
    protected $logger;
    protected $resultRedirectFactory;
    protected $eventService;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        SdkClientFactory $sdkClientFactory,
        PaymentResponseHandler $paymentResponseHandler,
        EventService $eventService,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderRepository        = $orderRepository;
        $this->sdkClientFactory       = $sdkClientFactory;
        $this->paymentResponseHandler = $paymentResponseHandler;
        $this->logger                 = $logger;
        $this->eventService           = $eventService;
        $this->resultRedirectFactory  = $context->getResultRedirectFactory();
    }

    public function execute()
    {
        $orderId        = $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $order = $this->orderRepository->get($orderId);
            if (! $order) {
                throw new \Exception('Order not found.');
            }

            if ($order->getState() !== Order::STATE_PENDING_PAYMENT) {
                $this->messageManager->addWarningMessage(__('Order is no longer in a pending state.'));
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
            }

            $merchantTransactionId = $order->getPayment()->getAdditionalInformation('merchant_order_id');
            if (! $merchantTransactionId) {
                $this->logger->critical('CRITICAL: Merchant Order ID not found for Order ' . $order->getIncrementId() . '. Canceling order.');

                if ($order->canCancel()) {
                    $order->cancel();
                    $order->addStatusHistoryComment('Order was automatically canceled because the PhonePe Merchant Transaction ID was missing.');

                    $this->orderRepository->save($order);
                }
                throw new \Exception('Merchant Transaction ID not found for this order.');
            }

            $this->logger->info('[PhonePe Admin] Manual status check initiated for order: ' . $order->getIncrementId());

            $client   = $this->sdkClientFactory->getClient();
            $response = (array) $client->getOrderStatus($merchantTransactionId);

            $this->paymentResponseHandler->handle($response, $order);

            $this->messageManager->addSuccessMessage(__('PhonePe status check complete. The order has been updated.'));

        } catch (\Exception $e) {
            $this->logger->error('[PhonePe Admin] Manual status check failed for order ID ' . $orderId, ['error' => $e->getMessage()]);
            $this->messageManager->addErrorMessage(__('PhonePe status check failed: %1', $e->getMessage()));
            
            try {
                $orderToCancel = $this->orderRepository->get($orderId); // Reload order object
                if ($orderToCancel && $orderToCancel->canCancel()) {
                    $orderToCancel->cancel();
                    $orderToCancel->addStatusHistoryComment(
                        __('Order automatically canceled because the manual status check failed. Reason: %1', $e->getMessage())
                    );
                    $this->orderRepository->save($orderToCancel);
                    $this->messageManager->addWarningMessage(__('The order has been canceled due to environment mismatch. Please verify the order status with PhonePe support if needed.'));
                }
            } catch (\Exception $cancelException) {
                $this->logger->critical(
                    '[PhonePe Admin] Failed to cancel order ID ' . $orderId . ' after status check failure.',
                    ['original_error' => $e->getMessage(), 'cancel_error' => $cancelException->getMessage()]
                );
                $this->messageManager->addErrorMessage(__('An error also occurred while trying to automatically cancel the order.'));
            }
	}

        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
