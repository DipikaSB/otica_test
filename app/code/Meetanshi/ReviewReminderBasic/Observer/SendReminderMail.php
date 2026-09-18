<?php

namespace Meetanshi\ReviewReminderBasic\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Meetanshi\ReviewReminderBasic\Helper\Data;

/**
 * Observer to send review reminder email when order status changes to complete.
 */
class SendReminderMail implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * SendReminderMail constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Execute observer - send review reminder when order reaches a configured status.
     *
     * @param Observer $observer
     *
     * @return bool|string
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            $storeId = $order->getStoreId();
            $allowedStatuses = $this->helper->getOrderStatuses($storeId);

            // Only proceed if order status is one of the configured statuses
            if (!in_array($order->getStatus(), $allowedStatuses)) {
                return true;
            }

            // Prevent re-triggering if order already had an allowed status before this save
            if (in_array($order->getOrigData('status'), $allowedStatuses)) {
                return true;
            }

            if ($this->helper->getConfig($storeId)) {
                if ($this->helper->getDays($storeId) == 0) {
                    $config = [];
                    $config['incrementId'] = $order->getIncrementId();
                    $config['mail'] = $order->getCustomerEmail();
                    $config['customer'] = $order->getBillingAddress()->getFirstName();
                    $config['customer_name'] = $order->getBillingAddress()->getFirstName();
                    $config['date'] = date('M d, Y h:i:s A', strtotime($order->getCreatedAt()));
                    $config['storeId'] = $storeId;
                    $product = [];
                    foreach ($order->getAllVisibleItems() as $item) {
                        if ($item->getParentItemId() == '') {
                            $product[] = $item->getProductId();
                        }
                    }
                    $config['reminder']['product_id'] = implode(',', $product);
                    $config['reminder']['increment_id'] = $order->getIncrementId();
                    $config['reminder']['customer_name'] = $order->getBillingAddress()->getFirstName();
                    $this->helper->sendReviewReminderMail($config);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
}
