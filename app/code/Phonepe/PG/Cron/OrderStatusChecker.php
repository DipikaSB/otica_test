<?php
namespace Phonepe\PG\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Phonepe\PG\Model\Constants\StatusConstants;
use Phonepe\PG\Model\ResourceModel\OrderStatusCheck\CollectionFactory as StatusCheckCollectionFactory;
use Phonepe\PG\Service\PaymentResponseHandler;
use Phonepe\PG\Service\SdkClientFactory;
use Psr\Log\LoggerInterface;

class OrderStatusChecker
{
    private const MAX_CHECK_RETRIES = 30;

    protected $logger;
    protected $orderRepository;
    protected $statusCheckCollectionFactory;
    protected $sdkClientFactory;
    protected $paymentResponseHandler;

    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        StatusCheckCollectionFactory $statusCheckCollectionFactory,
        SdkClientFactory $sdkClientFactory,
        PaymentResponseHandler $paymentResponseHandler
    ) {
        $this->logger                       = $logger;
        $this->orderRepository              = $orderRepository;
        $this->statusCheckCollectionFactory = $statusCheckCollectionFactory;
        $this->sdkClientFactory             = $sdkClientFactory;
        $this->paymentResponseHandler       = $paymentResponseHandler;
    }

    public function execute()
    {
        $this->logger->info('[PhonePe Cron] Starting order status check.');
        $collection = $this->statusCheckCollectionFactory->create();

        if ($collection->getSize() === 0) {
            $this->logger->info('[PhonePe Cron] No pending orders in the queue to check.');
            return;
        }

        $this->logger->info('[PhonePe Cron] Found ' . $collection->getSize() . ' order(s) to check.');

        foreach ($collection as $statusCheck) {
            $this->processStatusCheck($statusCheck);
        }
        $this->logger->info('[PhonePe Cron] Finished order status check.');
    }

    /**
     * Processes a single order from the check queue.
     * @param \Phonepe\PG\Model\OrderStatusCheck $statusCheck
     */
    private function processStatusCheck($statusCheck)
    {
        $orderId = $statusCheck->getOrderId();
        try {
            $currentCheckCount = (int) $statusCheck->getCheckCount();
            $statusCheck->setCheckCount($currentCheckCount + 1)->save();

            if ($statusCheck->getCheckCount() > self::MAX_CHECK_RETRIES) {
                $this->logger->warning('[PhonePe Cron] Max retries (' . self::MAX_CHECK_RETRIES . ') reached for order ' . $orderId . '. Removing from queue.');
                $statusCheck->delete();
                return;
            }

            $order = $this->orderRepository->get($orderId);

            if (in_array($order->getState(), StatusConstants::MAGENTO_FINAL_STATES)) {
                $this->logger->info('[PhonePe Cron] Order ' . $order->getIncrementId() . ' is no longer pending. Removing from queue.');
                $statusCheck->delete();
                return;
            }

            $this->logger->info('[PhonePe Cron] Checking status for order ' . $order->getIncrementId() . ' (Attempt #' . $statusCheck->getCheckCount() . ')');
            $client   = $this->sdkClientFactory->getClient();
            $response = (array) $client->getOrderStatus($statusCheck->getMerchantTransactionId());
            $status   = strtoupper($response['state'] ?? 'UNKNOWN');

            if (in_array($status, StatusConstants::FINAL_STATUSES)) {
                $this->logger->info('[PhonePe Cron] Received final status (' . $status . ') for order ' . $order->getIncrementId() . '. Updating and removing from queue.');
                $this->paymentResponseHandler->handle($response, $order);
            } else {
                $this->logger->info('[PhonePe Cron] Order ' . $order->getIncrementId() . ' is still PENDING. It will be checked again on the next run.');
            }
        } catch (\Exception $e) {
            $this->logger->error('[PhonePe Cron] Error processing order check for Magento Order ID ' . $order->getIncrementId(), ['message' => $e->getMessage()]);
        }
    }
}
