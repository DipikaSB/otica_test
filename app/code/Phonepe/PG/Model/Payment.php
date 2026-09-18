<?php
namespace Phonepe\PG\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger as PaymentLogger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Phonepe\PG\Service\PaymentInitiator;

class Payment extends AbstractMethod
{
    const PAYMENT_METHOD_CODE = 'phonepe_pg';

    protected $_code           = self::PAYMENT_METHOD_CODE;
    protected $_canAuthorize   = true;
    protected $_canCapture     = true;
    protected $_canUseCheckout = true;

    /**
     * This flag tells Magento to call our initialize() method instead of
     * automatically setting the order state to "Processing".
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var PaymentInitiator */
    protected $paymentInitiator;

    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $urlBuilder,
        PaymentHelper $paymentData,
        ScopeConfigInterface $scopeConfig,
        PaymentLogger $logger,
        ExtensionAttributesFactory $extFactory,
        AttributeValueFactory $attrFactory,
        PaymentInitiator $paymentInitiator,
        array $data = []
    ) {
        parent::__construct(
            $context, $registry, $extFactory, $attrFactory,
            $paymentData, $scopeConfig, $logger, null, null, $data
        );

        $this->urlBuilder       = $urlBuilder;
        $this->paymentInitiator = $paymentInitiator;
    }

    /**
     * This method is now called during order placement.
     * We explicitly set the state to "Pending Payment" here.
     *
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);

        return $this;
    }

    public function isAvailable(CartInterface $quote = null): bool
    {
        return $this->getConfigData('active') && parent::isAvailable($quote);
    }

    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('phonepe/redirect', ['_secure' => true]);
    }

    public function order(InfoInterface $payment, $amount)
    {
        $merchantTransactionId = $payment->getOrder()->getIncrementId() . '_' . time();

        $payment->setTransactionId($merchantTransactionId)
            ->setAdditionalInformation('merchant_transaction_id', $merchantTransactionId)
            ->setIsTransactionPending(true);

        return $this;
    }

    public function initiatePayment($order): array
    {
        return $this->paymentInitiator->initiate($order);
    }
}
