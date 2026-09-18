<?php
namespace Phonepe\PG\Controller\Index;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Phonepe\PG\Controller\BaseController;
use Phonepe\PG\Helper\Data;
use Phonepe\PG\Model\Constants\MessageConstants;
use Phonepe\PG\Model\OrderStatusCheckFactory;
use Phonepe\PG\Model\Payment;
use Psr\Log\LoggerInterface;

class Redirect extends BaseController
{
    protected $redirectFactory;
    protected $paymentModel;
    protected $statusCheckFactory;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Data $helper,
        Payment $paymentModel,
        RedirectFactory $redirectFactory,
        OrderStatusCheckFactory $statusCheckFactory

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
        $this->redirectFactory    = $redirectFactory;
        $this->paymentModel       = $paymentModel;
        $this->statusCheckFactory = $statusCheckFactory;
    }

    public function execute()
    {
        $order  = $this->checkoutSession->getLastRealOrder();
        $result = $this->resultJsonFactory->create();

        if (! $order || ! $order->getId()) {
            $this->logError('No valid order found in session for redirect');
            return $result->setData(['error' => true, 'message' => 'No valid order found in session.']);
        }

        try {
            $paymentResponse = $this->paymentModel->initiatePayment($order);

            if (isset($paymentResponse['redirectUrl'])) {
                $this->logDebug('Payment initiated successfully', [
                    'order_id' => $order->getIncrementId(),
                ]);

                $order->getPayment()->setAdditionalInformation('merchant_order_id', $paymentResponse['merchantOrderId']);
                $order->save();

                $merchantOrderId = $paymentResponse['merchantOrderId'];

                $statusCheck = $this->statusCheckFactory->create();

                $statusCheck->setData([
                    'order_id'                => $order->getId(),
                    'merchant_transaction_id' => $merchantOrderId,
                    'check_count'             => 0,
                ]);
                $statusCheck->save();

                return $result->setData([
                    'success'     => true,
                    'redirectUrl' => $paymentResponse['redirectUrl'],
                ]);
            } else {
                $this->logError('Redirect URL not present in PhonePe response', $paymentResponse);
                return $result->setData([
                    'success' => false,
                    'message' => __(MessageConstants::ERROR_REDIRECT_URL_MISSING),
                ]);
            }
        } catch (\Exception $e) {
            $this->logError('Payment redirect failed: ' . $e->getMessage());
            $this->checkoutSession->restoreQuote();
            $isProduction = $this->helper->getEnvironment() === 'production';

            $message = $isProduction
            ? __(MessageConstants::ERROR_GENERIC_PAYMENT_FAILURE)
            : 'Payment redirect failed: ' . $e->getMessage();

            return $result->setData([
                'success' => false,
                'message' => $message,
            ]);
        }
    }
}
