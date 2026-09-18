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

namespace Ecomteck\GuestToCustomer\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ecomteck\GuestToCustomer\Helper\Data;

/**
 * Class Index
 * @package Ecomteck\GuestToCustomer\Controller\Adminhtml\Customer
 */
class Index extends Action
{
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
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param Session $authSession
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        Session $authSession,
        Data $helperData
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->authSession = $authSession;
        $this->helperData = $helperData;
        
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Json|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $is_ajax = true;
        if(!$orderId){
            $orderId = $request->getParam('order_id', null);
            $is_ajax = false;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->resultJsonFactory->create();

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
                    $this->messageManager->addSuccessMessage(__('Customer Account was successfully created.'));
                } elseif ($this->helperData->isMergeIfCustomerAlreadyExists()) {
                    /** @var  $customer CustomerRepositoryInterface */
                    $customer = $this->customerRepository->get($order->getCustomerEmail());
                    
                } else {
                    if($is_ajax){
                        return $resultJson->setData(
                            $this->getMessage(true, 'Customer with email address already exists')
                        );
                    } else {
                        return $resultRedirect->setPath('sales/order/index');
                    }
                }

                $this->helperData->setCustomerData($order, $customer);

                $comment = sprintf(
                    __("Guest order converted by admin user: %s"),
                    $this->authSession->getUser()->getUserName()
                );

                $order->addStatusHistoryComment($comment);

                $this->orderRepository->save($order);

                $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());

                $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));
                if($is_ajax){
                    return $resultJson->setData($this->getMessage(false, 'Order was successfully converted.'));
                } else {
                    return $resultRedirect->setPath('sales/order/index');
                }
            } catch (Exception $e) {
                if($is_ajax){
                    return $resultJson->setData($this->getMessage(true, $e->getMessage()));
                } else {
                    return $resultRedirect->setPath('sales/order/index');
                }
            }
        } else {
            if($is_ajax){
                return $resultJson->setData($this->getMessage(true, 'Invalid order id.'));
            } else {
                return $resultRedirect->setPath('sales/order/index');
            }
        }
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomteck_GuestToCustomer::convert_button');
    }

    protected function getMessage($hasError, $message)
    {
        return [
            'error' => $hasError,
            'message' => __($message)
        ];
    }
}
