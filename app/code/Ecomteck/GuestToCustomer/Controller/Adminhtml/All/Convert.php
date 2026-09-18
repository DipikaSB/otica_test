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

namespace Ecomteck\GuestToCustomer\Controller\Adminhtml\All;

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
 * Class Convert
 * @package Ecomteck\GuestToCustomer\Controller\Adminhtml\All
 */
class Convert extends Action
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

    protected $_orderCollectionFactory;

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
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        Session $authSession,
        Data $helperData,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->authSession = $authSession;
        $this->helperData = $helperData;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    private function getOrderCollection(){
        $collection = $this->_orderCollectionFactory->create()
                            ->addAttributeToSelect('entity_id')
                            ->addFieldToFilter('customer_id', array('null' => true));
        return $collection;
    }

    /**
     * Index action
     * @return Json
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order_collection = $this->getOrderCollection();
        if($order_collection->count()){
            foreach($order_collection as $order_entity){
                /** @var  $order OrderInterface */
                $orderId = $order_entity->getData("entity_id");
                if($orderId){
                    $order = $this->orderRepository->get($orderId);
                    if ($orderId && $order->getEntityId()) {
                        try {
                            if ($this->accountManagement->isEmailAvailable($order->getCustomerEmail())) {
                                $customer = $this->orderCustomerService->create($orderId);
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

                            }elseif ($this->helperData->isMergeIfCustomerAlreadyExists()) {
                                /** @var  $customer CustomerRepositoryInterface */
                                $customer = $this->customerRepository->get($order->getCustomerEmail());
                            } else {
                                return $resultRedirect->setPath('sales/order/index');
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
                        } catch (Exception $e) {
                            
                        }
                    } else {
                       continue;
                    }
                }
            }
        }

        return $resultRedirect->setPath('customer/index/index');
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
