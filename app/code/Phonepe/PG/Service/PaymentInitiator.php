<?php
namespace Phonepe\PG\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use Phonepe\PG\Helper\Data;
use Phonepe\PG\Model\Constants\EventConstants;
use Phonepe\PG\Service\EventService;
use Psr\Log\LoggerInterface;

class PaymentInitiator
{
    protected $dataHelper;
    protected $sdkClientFactory;
    protected $eventService;
    protected $logger;

    public function __construct(
        Data $dataHelper,
        SdkClientFactory $sdkClientFactory,
        EventService $eventService,
        LoggerInterface $logger
    ) {
        $this->dataHelper       = $dataHelper;
        $this->sdkClientFactory = $sdkClientFactory;
        $this->eventService     = $eventService;
        $this->logger           = $logger;
    }

    /**
     * @param Order $order
     * @return array
     * @throws LocalizedException
     */
    public function initiate($order)
    {
        $merchantOrderId = $this->dataHelper->generateMerchantTransactionId($order->getIncrementId());
        $amountInPaisa   = (int) round($order->getGrandTotal() * 100);

        $redirectUrl = $this->dataHelper->getRedirectUrl();

        $this->logger->info("Initiating payment event for Order #" . $order->getIncrementId() . " with Merchant Order ID: " . $merchantOrderId);
        $this->eventService->sendEvent(EventConstants::PAYMENT_REQUEST_TRIGGERED_FROM_PLUGIN, [], $merchantOrderId);

        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($merchantOrderId)
            ->amount($amountInPaisa)
            ->redirectUrl($redirectUrl)
            ->message("Magento PhonePe PG plugin Payment for Order #" . $order->getIncrementId())
            ->build();

        try {
            $client = $this->sdkClientFactory->getClient();
            $this->eventService->sendEvent(EventConstants::PLUGIN_HAS_LAUNCHED_PAY_PAGE, [], $merchantOrderId);
            $response = $client->pay($request);
        } catch (PhonePeException $e) {
            throw new LocalizedException(__('PhonePe SDK error: %1', $e->getMessage()));
        }

        if ($response->getState() !== 'PENDING') {
            throw new LocalizedException(__('Payment initialization failed: %1', $response->getState()));
        }

        $order->getPayment()->setAdditionalInformation('merchant_order_id', $merchantOrderId);
        $order->getPayment()->save();

        return [
            'redirectUrl'     => $response->getRedirectUrl(),
            'merchantOrderId' => $merchantOrderId,
        ];
    }
}
