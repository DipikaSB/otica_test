<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Controller\Index;

use Setblue\CustomerWholesaleInquiry\Model\OtpVerficationFactory;
use Setblue\CustomerWholesaleInquiry\Model\WholesaleInquiryFactory;
use Magento\Framework\Controller\ResultFactory;

class Savedataforend extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_OtpVerficationFactory;
    protected $_WholesaleInquiryFactory;
    protected $_messageManager;
    protected $resultFactory;
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        OtpVerficationFactory $OtpVerficationFactory,
        WholesaleInquiryFactory $WholesaleInquiryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        array $data = []
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_OtpVerficationFactory = $OtpVerficationFactory;
        $this->_WholesaleInquiryFactory = $WholesaleInquiryFactory;
        $this->_messageManager = $messageManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        return parent::__construct($context);
    }
    public function execute()
    {
        try {
            date_default_timezone_set("Asia/Kolkata");

            // Get request parameters
            $fname      = trim((string)$this->getRequest()->getParam('fname'));
            $lname      = trim((string)$this->getRequest()->getParam('lname'));
            $phone      = trim((string)$this->getRequest()->getParam('phone'));
            $email      = trim((string)$this->getRequest()->getParam('email_id'));
            $country    = trim((string)$this->getRequest()->getParam('country'));
            $productSku = trim((string)$this->getRequest()->getParam('productsku'));
            $comment    = trim((string)$this->getRequest()->getParam('comment'));

            // Required field validation
            if ($fname === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'First name is required.'
                    ]);
            }

            if ($lname === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Last name is required.'
                    ]);
            }

            if ($phone === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Phone number is required.'
                    ]);
            }

            if ($email === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Email is required.'
                    ]);
            }

            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Please enter a valid email address.'
                    ]);
            }

            if ($country === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Country is required.'
                    ]);
            }

            if ($productSku === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Product SKU is required.'
                    ]);
            }

            if ($comment === '') {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'Comment is required.'
                    ]);
            }

            // OTP verification record
            $verification = $this->_OtpVerficationFactory->create();
            $collectioncat = $verification->getCollection()->getLastItem();

            if (!$collectioncat->getId()) {
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => 0,
                        'message' => 'OTP verification is required.'
                    ]);
            }

            // Data to save
            $data = [
                'fname'        => $fname,
                'lname'        => $lname,
                'phone_no'     => $phone,
                'email'        => $email,
                'country'      => $country,
                'product_sku'  => $productSku,
                'comment'      => $comment,
                'otp_id'       => $collectioncat->getId(),
                'created_date' => date("Y-m-d H:i:s")
            ];

            // Save only after all validation passes
            $CustomerWholesaleInquiry = $this->_WholesaleInquiryFactory->create();
            $CustomerWholesaleInquiry->setData($data);
            $CustomerWholesaleInquiry->save();

            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => 1,
                    'message' => 'Wholesale Inquiry Submit Success'
                ]);

        } catch (\Exception $e) {

            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => 0,
                    'message' => 'Something went wrong.'
                ]);
        }
    }
}
