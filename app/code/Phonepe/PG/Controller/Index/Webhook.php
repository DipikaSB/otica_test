<?php
namespace Phonepe\PG\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Phonepe\PG\Controller\BaseController;
use Phonepe\PG\Helper\Data;
use Phonepe\PG\Service\PaymentResponseHandler;
use Phonepe\PG\Service\PhonePeCallbackVerifier;
use Psr\Log\LoggerInterface;

class Webhook extends BaseController
{
    protected $request;
    protected $responseHandler;
    protected $callbackVerifier;
    protected $orderCollectionFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        PageFactory $pageFactory,
        LoggerInterface $logger,
        Data $helper,
        PaymentResponseHandler $responseHandler,
        HttpRequest $request,
        PhonePeCallbackVerifier $callbackVerifier,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderRepository,
            $resultJsonFactory,
            $pageFactory,
            $logger,
            $helper
        );

        $this->responseHandler        = $responseHandler;
        $this->request                = $request;
        $this->callbackVerifier       = $callbackVerifier;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    private function getOrderByMerchantTransactionId(string $merchantTransactionId)
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->join(
            ['sop' => 'sales_order_payment'],
            'main_table.entity_id = sop.parent_id',
            []
        );

        $collection->addFieldToFilter(
            'sop.additional_information',
            ['like' => '%"merchant_order_id":"' . $merchantTransactionId . '"%']
        );

        $order = $collection->getFirstItem();

        if (! $order || ! $order->getId()) {
            throw new \Magento\Framework\Exception\NotFoundException(
                __('Order with Merchant Transaction ID "%1" not found.', $merchantTransactionId)
            );
        }

        return $order;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $payload = $this->request->getContent();
            $headers = $this->request->getHeaders()->toArray();

            $this->logger->info('Received Webhook', ['headers' => $headers, 'payload' => $payload]);

            $verifiedData          = $this->callbackVerifier->verify($headers, $payload);
            $merchantTransactionId = $verifiedData['data']['merchantTransactionId'] ?? null;

            if (! $merchantTransactionId) {
                $this->logError('Webhook: Missing merchantTransactionId in verified payload', ['payload' => $verifiedData]);
                return $result->setData(['success' => false, 'message' => 'Missing merchantTransactionId']);
            }

            $order = $this->getOrderByMerchantTransactionId($merchantTransactionId);
            $this->responseHandler->handle($verifiedData, $order);
            $this->logDebug('Webhook processed successfully', ['order_id' => $order->getIncrementId()]);

            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $this->logError('Webhook processing failed', ['error' => $e->getMessage()]);
            return $result->setHttpResponseCode(400)->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
