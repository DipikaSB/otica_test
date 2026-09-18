<?php

namespace Setblue\OrderCancelEmail\Cron;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class SendCancelEmail
{
    protected $transportBuilder;
    protected $orderCollectionFactory;
    protected $orderRepository;
    protected $logger;
    protected $resource;

    public function __construct(
        TransportBuilder $transportBuilder,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        ResourceConnection $resource
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->resource = $resource;
    }

    public function execute()
    {
        try {
            $orders = $this->orderCollectionFactory->create()
                ->addFieldToFilter('state', 'canceled')
                ->addFieldToFilter('cancel_email_sent', 0);

            if (!$orders->getSize()) {
                $this->logger->info('No cancelled orders found.');
                return;
            }

            foreach ($orders as $order) {

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
                            'area' => Area::AREA_FRONTEND,
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

                    // Update flag
                    $order->setData('cancel_email_sent', 1);
                    $this->orderRepository->save($order);

                    $connection = $this->resource->getConnection();
                    $connection->update( 
                        $this->resource->getTableName('sales_order_grid'), 
                        ['cancel_email_sent' => 1], 
                        ['entity_id = ?' => $order->getId()] 
                    );

                } catch (\Exception $e) {

                    $this->logger->info($e->getMessage());
                }
            }

            $log->info('Cancel Order Email Cron Completed');

        } catch (\Exception $e) {

            $this->logger->info($e->getMessage());

        }
    }
}