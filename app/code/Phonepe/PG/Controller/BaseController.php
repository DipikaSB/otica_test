<?php
namespace Phonepe\PG\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Phonepe\PG\Helper\Data as PhonePeHelper;

abstract class BaseController extends Action
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $logger;
    protected $helper;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        PhonePeHelper $helper
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    protected function getOrderByIncrementId($incrementId)
    {
        return $this->orderRepository->get($incrementId);
    }

    protected function logDebug($message, array $data = [])
    {
        $this->logger->debug('[PhonePe] ' . $message, $data);
    }

    protected function logError($message, array $data = [])
    {
        $this->logger->error('[PhonePe] ' . $message, $data);
    }
}
