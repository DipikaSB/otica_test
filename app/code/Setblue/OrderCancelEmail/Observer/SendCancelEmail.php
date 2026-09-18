<?php

namespace Setblue\OrderCancelEmail\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class SendCancelEmail implements ObserverInterface
{
    protected $transportBuilder;
    protected $storeManager;
    protected $scopeConfig;
    protected $logger;
    protected $resource;

    public function __construct(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        ResourceConnection $resource
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->resource = $resource;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return;
        }

        if ($order->getState() != Order::STATE_CANCELED) {
            return;
        }

        // Prevent duplicate emails
        if ($order->getData('cancel_email_sent')) {
            return;
        }

        try {
            $customerFirstName = $order->getCustomerFirstname();
            $customerLastName = $order->getCustomerLastname();

            $baseUrl = rtrim(
                $order->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
                '/'
            );

            $recoveryLink = $baseUrl . '/smordercancel/recovery/index/order_id/' . $order->getId();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('setblue_order_cancel_email')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $order->getStoreId()
                ])
                ->setTemplateVars([ 
                    'order' => $order, 
                    'customer_name' => $customerFirstName.' '.$customerLastName, 
                    'order_number' => $order->getIncrementId(), 
                    'recovery_link' => $recoveryLink, 
                    'store_url' => $order->getStore()->getBaseUrl()
                ])
                ->setFromByScope('general')
                ->addTo(
                    $order->getCustomerEmail(),
                    $order->getCustomerName()
                )
                ->getTransport();

            $transport->sendMessage();

            // Mark email as sent
            $order->setData('cancel_email_sent', 1);
            $order->save();

            $connection = $this->resource->getConnection();
            $connection->update( 
                $this->resource->getTableName('sales_order_grid'), 
                ['cancel_email_sent' => 1], 
                ['entity_id = ?' => $order->getId()] 
            );

        } catch (\Exception $e) {
            $this->logger->error(
                '[Order Cancel Email Failure]',
                [
                    'message' => $e->getMessage(),
                    'order_id' => $order->getIncrementId()
                ]
            );
        }
    }
}