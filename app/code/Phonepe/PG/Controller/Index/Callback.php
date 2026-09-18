<?php
namespace Phonepe\PG\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Phonepe\PG\Controller\BaseController;
use Phonepe\PG\Helper\Data;
use Phonepe\PG\Model\Constants\EventConstants;
use Phonepe\PG\Model\Constants\StatusConstants;
use Phonepe\PG\Model\OrderStatusCheckFactory;
use Phonepe\PG\Model\Payment;
use Phonepe\PG\Model\ResourceModel\OrderStatusCheck\CollectionFactory as StatusCheckCollectionFactory;
use Phonepe\PG\Service\EventService;
use Phonepe\PG\Service\PaymentResponseHandler;
use Phonepe\PG\Service\SdkClientFactory;
use Psr\Log\LoggerInterface;

class Callback extends BaseController
{
    protected $resultRedirectFactory;
    protected $sdkClientFactory;
    protected $statusCheckFactory;
    protected $paymentResponseHandler;
    protected $statusCheckCollectionFactory;
    protected $eventService;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Data $helper,
        Payment $paymentModel,
        RedirectFactory $resultRedirectFactory,
        SdkClientFactory $sdkClientFactory,
        OrderStatusCheckFactory $statusCheckFactory,
        PaymentResponseHandler $paymentResponseHandler,
        StatusCheckCollectionFactory $statusCheckCollectionFactory,
        EventService $eventService,
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderRepository,
            $resultJsonFactory,
            $resultPageFactory,
            $logger,
            $helper,
            $paymentModel
        );

        $this->resultRedirectFactory        = $resultRedirectFactory;
        $this->sdkClientFactory             = $sdkClientFactory;
        $this->statusCheckFactory           = $statusCheckFactory;
        $this->paymentResponseHandler       = $paymentResponseHandler;
        $this->statusCheckCollectionFactory = $statusCheckCollectionFactory;
        $this->eventService                 = $eventService;
    }

    public function execute()
    {
        $this->logDebug('User returned to callback. Starting immediate status check.');

        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if (! $order || ! $order->getId()) {
                throw new \Exception('No valid order found in session for status check.');
            }

            $merchantOrderId = $order->getPayment()->getAdditionalInformation('merchant_order_id');
            if (! $merchantOrderId) {
                throw new \Exception('Could not retrieve merchant order ID from order.');
            }

            $client   = $this->sdkClientFactory->getClient();
            $response = $client->getOrderStatus($merchantOrderId);
            $status   = strtoupper($response->getState());

            if ($response) {
                $this->eventService->sendEvent(EventConstants::PAYMENT_RESPONSE_RECEIVED_AT_PLUGIN, [
                    'status' => $status,
                ], $merchantOrderId);
            }

            $this->logDebug('Immediate status check result for order ' . $order->getIncrementId(), ['status' => $status]);

            $this->eventService->sendEvent(EventConstants::PLUGIN_STATUS_CHECK, [], $merchantOrderId);

            if ($status === StatusConstants::COMPLETED) {
                $this->paymentResponseHandler->handle((array) $response, $order);
                $this->removeFromQueue($order);
                $this->eventService->sendEvent(EventConstants::PLUGIN_HAS_GIVEN_CONTROL_BACK_TO_MERCHANT, [], $merchantOrderId);
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
            } elseif ($status === StatusConstants::FAILED) {
                $this->paymentResponseHandler->handle((array) $response, $order);
                $this->removeFromQueue($order);
                $this->eventService->sendEvent(EventConstants::PLUGIN_HAS_GIVEN_CONTROL_BACK_TO_MERCHANT, [], $merchantOrderId);
                return $this->redirectToCartWithError('Your payment has failed. Please try again.');
            } else {
                $statusCheck = $this->statusCheckFactory->create();
                $statusCheck->setOrderId($order->getId());
                $this->eventService->sendEvent(EventConstants::PLUGIN_HAS_GIVEN_CONTROL_BACK_TO_MERCHANT, [], $merchantOrderId);
                $statusCheck->setMerchantOrderId($merchantOrderId);
                $statusCheck->save();

                $this->messageManager->addNoticeMessage(__('Your payment is pending. We will notify you once the status is confirmed.'));
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }

        } catch (\Exception $e) {
            $this->logError('Callback processing failed with an exception.', ['error' => $e->getMessage()]);
            return $this->redirectToCartWithError('An unexpected error occurred while processing your payment.');
        }
    }

    protected function redirectToCartWithError($message)
    {
        $this->checkoutSession->restoreQuote();
        $this->messageManager->addErrorMessage(__($message));
        return $this->resultRedirectFactory->create()->setPath('checkout/cart');
    }

    private function removeFromQueue(\Magento\Sales\Model\Order $order)
    {
        try {
            $collection = $this->statusCheckCollectionFactory->create();
            $collection->addFieldToFilter('order_id', $order->getId());
            foreach ($collection as $item) {
                $item->delete();
                $this->logDebug('Removed order ' . $order->getIncrementId() . ' from the polling queue.');
            }
        } catch (\Exception $e) {
            $this->logError('Could not remove order ' . $order->getIncrementId() . ' from polling queue.', ['error' => $e->getMessage()]);
        }
    }
}
