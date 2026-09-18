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

namespace Ecomteck\GuestToCustomer\Controller\Adminhtml\Edit;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Validator\EmailAddress;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Index
 * @package Ecomteck\GuestToCustomer\Controller\Adminhtml\Edit
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
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var EmailAddress
     */
    private $emailAddressValidator;
    /**
     * @var Session
     */
    private $authSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailAddress $emailAddressValidator
     * @param Session $authSession
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository,
        EmailAddress $emailAddressValidator,
        Session $authSession
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->emailAddressValidator = $emailAddressValidator;
        $this->authSession = $authSession;
    }

    /**
     * Index action
     * @return Json
     * @throws Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id');
        $customerId = trim($request->getPost('customer_id'));
        $oldCustomerId = trim($request->getPost('old_customer_id'));
        $emailAddress = trim($request->getPost('email'));
        $oldEmailAddress = $request->getPost('old_email');
        $resultJson = $this->resultJsonFactory->create();

        if (!isset($orderId)) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid order id.'),
                    'customer_id' => '',
                    'ajaxExpired' => false
                ]
            );
        }
        $is_valid_customer_id = true;
        $is_valid_email = true;

        if (!$customerId || !is_numeric($customerId)) {
            $is_valid_customer_id = false;
            if (!$this->emailAddressValidator->isValid($emailAddress)) {
                $is_valid_email = false;
            }else {
                $customer = $this->customerRepository->get($emailAddress);
                $customerId = $customer->getId();
            }
        }

        if (!$customerId || !is_numeric($customerId)) {
            $is_valid_customer_id = false;
            $is_valid_email = false;
        }

        if(!$is_valid_email && !$is_valid_customer_id){
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid Email address or Customer ID. You should input email address or customer id correctly.'),
                    'email' => '',
                    'ajaxExpired' => false
                ]
            );
        }


        try {
            /** @var  $order OrderInterface */
            $order = $this->orderRepository->get($orderId);
            if ($order->getEntityId() && (($order->getCustomerId() == $oldCustomerId) || (!$order->getCustomerId() && !$oldCustomerId))) {
                $comment = sprintf(
                    __("Order customer change from %s to %s by %s"),
                    $oldCustomerId,
                    $customerId,
                    $this->authSession->getUser()->getUserName()
                );

                $order->addStatusHistoryComment($comment);
                $order->setCustomerId($customerId);
                $this->orderRepository->save($order);
            }


            return $resultJson->setData(
                [
                    'error' => false,
                    'message' => __('Customer Info successfully changed.'),
                    'customer_id' => $customerId,
                    'ajaxExpired' => false
                ]
            );
        } catch (Exception $e) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => $e->getMessage(),
                    'customer_id' => '',
                    'ajaxExpired' => false
                ]
            );
        }
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecomteck_GuestToCustomer::change_customer');
    }
}
