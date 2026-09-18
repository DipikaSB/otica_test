<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_GuestToCustomer
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\GuestToCustomer\Observer;

use Exception;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ecomteck\GuestToCustomer\Helper\Data;

/**
 * Class AutoCreateCustomerAccount
 * @package Ecomteck\GuestToCustomer\Observer
 */
class AutoCreateCustomerAccount implements ObserverInterface
{
    /**
     * @var PurchasedFactory
     */
    protected $purchasedFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Index constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Data $helperData
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Data $helperData
    ) {
        $this->orderRepository      = $orderRepository;
        $this->accountManagement    = $accountManagement;
        $this->customerRepository   = $customerRepository;
        $this->helperData           = $helperData;
        $this->messageManager       = $messageManager;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        if($this->helperData->isAutoConvertGuestToCustomer()){
            $orderIds = $observer->getEvent()->getOrderIds();
            $orderId = (int)$orderIds[0];
            /** @var  $order OrderInterface */
            $order = $this->orderRepository->get($orderId);

            if ($orderId && $order->getEntityId()) {
                try {
                    if ($this->accountManagement->isEmailAvailable($order->getCustomerEmail())) { //Not exists customer will create new account
                        /** @var  $customer CustomerRepositoryInterface */
                        $customer = $this->helperData->convertGuestToCustomer($orderId);

                        if($this->helperData->isAssignOrderAddressToCustomer()){
                            //assign order address to customer
                            $this->helperData->saveCustomerAddress($customer->getId(), $order);
                        }
                        if($customer_group_id = $this->helperData->getAssignCustomerGroup()){
                            //assign customer group id
                            $customer->setGroupId((int)$customer_group_id);
                            $this->customerRepository->save($customer);
                        }

                        /**
                         * Reload the order to get fresh state after convertGuestToCustomer()
                         * which may have already saved the order internally via
                         * orderCustomerService->create().
                         */
                        $order = $this->orderRepository->get($orderId);

                        /**
                         * Always ensure the order is linked to the customer.
                         * Set customer_id and customer_is_guest regardless of merge config,
                         * so the order always appears in the customer's "My Orders" page.
                         */
                        $order->setCustomerId($customer->getId());
                        $order->setCustomerIsGuest(0);

                        if ($this->helperData->isMergeIfCustomerAlreadyExists()) {
                            // Apply additional customer data (group, name, DOB, gender, taxvat)
                            $this->helperData->setCustomerData($order, $customer);
                            $comment = __("Guest order converted automatically after checkout successfully.");
                            $order->addStatusHistoryComment($comment);
                            $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());
                        }

                        // Save the order once with all modifications applied
                        $this->orderRepository->save($order);

                        $this->messageManager->addSuccessMessage(__('We created the customer account for you. Check your email inbox to confirm the account. Then you can track your order status by login your account.'));
                    }
                } catch (Exception $e) {
                    //do nothing
                }
            }
        }     
    }
}
