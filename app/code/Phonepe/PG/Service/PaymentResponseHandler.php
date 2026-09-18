<?php
namespace Phonepe\PG\Service;

use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

class PaymentResponseHandler
{
    protected $orderRepository;
    protected $logger;
    protected $invoiceService;
    protected $transaction;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        InvoiceService $invoiceService,
        Transaction $transaction
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger          = $logger;
        $this->invoiceService  = $invoiceService;
        $this->transaction     = $transaction;
    }

    /**
     * Checks if all items in the order are virtual/digital.
     *
     * @param Order $order
     * @return bool
     */
    private function isVirtualOrder(Order $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            if (! $item->getIsVirtual()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Handle and verify the payment status for a given order
     */
    public function handle(array $response, Order $order)
    {
        try {
            $merchantOrderId = $order->getPayment()->getAdditionalInformation('merchant_order_id');
            if (! $merchantOrderId) {
                $this->logger->error('[PhonePe Response] Could not find merchantOrderId on the order.');
                throw new LocalizedException(__('Could not find merchantOrderId on the order.'));
            }

            $state = strtoupper($response['state'] ?? 'UNKNOWN');

            $this->logger->debug('[PhonePe Response] Handling payment response', [
                'merchantOrderId' => $merchantOrderId,
                'state'           => $state,
            ]);

            $payment = $order->getPayment();

            $this->logger->info(
                '[PhonePe Response] State ' . $state
            );

            switch ($state) {
                case 'COMPLETED':
                $phonepeOrderId = $response['orderId'] ?? null;

                if ($phonepeOrderId) {
                    $payment->setAdditionalInformation('phonepe_order_id', $phonepeOrderId);
                    $payment->setLastTransId($phonepeOrderId);

                    $this->logger->info(
                        '[PhonePe Response] Saved PhonePe Order ID ' .
                        $phonepeOrderId .
                        ' for Magento Order ' .
                        $merchantOrderId
                    );
                }

                /*
                 * Payment COMPLETED che, etle order ma koi pan item hoy
                 * to invoice generate karo.
                 */
                if ($order->canInvoice()) {
                    $this->logger->info(
                        '[PhonePe] Payment completed. Generating invoice.',
                        ['order_id' => $order->getIncrementId()]
                    );

                    $invoice = $this->invoiceService->prepareInvoice($order);

                    if ($invoice->getTotalQty() > 0) {
                        $invoice->setRequestedCaptureCase(
                            \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE
                        );

                        $invoice->register();

                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->transaction
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());

                        $transactionSave->save();

                        $this->logger->info(
                            '[PhonePe] Invoice generated successfully.',
                            [
                                'order_id'   => $order->getIncrementId(),
                                'invoice_id' => $invoice->getIncrementId()
                            ]
                        );
                    }
                } else {
                    $this->logger->info(
                        '[PhonePe] Invoice cannot be generated. Order may already be invoiced.',
                        ['order_id' => $order->getIncrementId()]
                    );
                }

                /*
                 * Invoice generate thai gaya pachi order status.
                 * Physical order -> processing
                 * Virtual order -> complete
                 */
                if ($this->isVirtualOrder($order)) {
                    $order->setState(Order::STATE_COMPLETE)
                        ->setStatus(Order::STATE_COMPLETE);
                } else {
                    $order->setState(Order::STATE_PROCESSING)
                        ->setStatus(Order::STATE_PROCESSING);
                }

                break;

                case 'PENDING':
                    $order->setState(Order::STATE_PENDING_PAYMENT)
                        ->setStatus(Order::STATE_PENDING_PAYMENT);
                    break;

                case 'FAILED':
                default:
                    $order->setState(Order::STATE_CANCELED)
                        ->setStatus(Order::STATE_CANCELED);
                    break;
            }

            $order->addStatusHistoryComment(__('PhonePe Payment Status: %1', $state));
            $this->orderRepository->save($order);

            $payment->save();
        } catch (\Exception $e) {
            $this->logger->error('[PhonePe Response] Error handling payment response', [
                'error' => $e->getMessage(),
            ]);
            throw new LocalizedException(__('Failed to process PhonePe payment response.'));
        }
    }
}
