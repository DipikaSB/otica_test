<?php
namespace Ecomteck\GuestToCustomer\Observer;

use Ecomteck\GuestToCustomer\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;

class OrderCheckoutSuccess implements ObserverInterface
{
    protected $helper;
    protected $customerFactory;
    protected $orderFactory;
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager,
                                Data $helper,
                                CustomerFactory $customerFactory,
                                OrderFactory $orderFactory)
    {
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->helper->isEnabled()){
            if($this->helper->isMergeIfCustomerAlreadyExists())
            {
                 $order = $observer->getOrder();
                 $currentCustomerId = $order->getData()['customer_id'];
                 $orderId = $order->getData()['entity_id'];
                 $currentCustomerEmail = $order->getData()['customer_email'];
                 if($currentCustomerId==null){
                      $customer = $this->customerFactory->create();
                       $customerCollection = $customer->getCollection()->addFieldToFilter('email',$currentCustomerEmail);
                       if($customerCollection)
                       {
                           $orderData = $this->orderFactory->create()->load($orderId);
                           $customerId = $customerCollection->getData()[0]['entity_id'];
                           $customerGroupId = $customerCollection->getData()[0]['group_id'];
                           $orderData->setCustomerId($customerId)
                               ->setCustomerIsGuest(0)
                               ->setCustomerGroupId($customerGroupId)->setSendEmail(null)->setEmailSent(null)->save();
                       }
                 }
            }
        }
    }
}
