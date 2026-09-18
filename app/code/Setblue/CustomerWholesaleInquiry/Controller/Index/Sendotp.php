<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_WholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Controller\Index;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Setblue\CustomerWholesaleInquiry\Model\OtpVerficationFactory;
use Setblue\CustomerWholesaleInquiry\Helper\Data;

/**
 * Class Helloworld
 *
 * @package MageSpark\HelloWorld\Controller\Index
 */
class Sendotp extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $_OtpVerficationFactory;
    protected $_customerSession;
    private $datahelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        ResultFactory $resultFactory,
        OtpVerficationFactory $OtpVerficationFactory,
        \Magento\Customer\Model\Session $customerSession,
        Data $datahelper
    ) {
        $this->pageFactory = $pageFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->resultFactory = $resultFactory;
        $this->_OtpVerficationFactory = $OtpVerficationFactory;
        $this->_customerSession = $customerSession;
        $this->datahelper = $datahelper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        date_default_timezone_set("Asia/Kolkata");
        if ($this->_customerSession->isLoggedIn()) {
            $email_id =  $this->_customerSession->getCustomer()->getEmail();
        } else {
            $email_id = $this->getRequest()->getParam('email_id');
        }
        $seed = str_split('0123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        /*echo $rand;*/
        foreach (array_rand($seed, 5) as $k) {
            $rand .= $seed[$k];
        }
        $CustomerName = $this->getRequest()->getParam('fname');
        $data = $this->getRequest()->getParams();
        $data['email'] = $email_id;
        $data['otp'] = $rand;
        $data['is_verified'] = 'Not Verify';
        $data['created_date'] = date("Y-m-d H:i:s");
        $OtpVerficationFactory = $this->_OtpVerficationFactory->create();
        $OtpVerficationFactory->setData($data);
        $OtpVerficationFactory->save();

        $variables = [
           'recipient_name' =>  $CustomerName,
            'recipient_email' => $email_id,
            'message' => $rand
            
        ];

        $this->inlineTranslation->suspend();
        try {
            $toEmail = 'owner@example.com';
            $toName = 'Owner';
            $emailTemplate = 'CustomerWholesaleInquiry_general_cls_email_template';

            $sender = [
            'email' => $toEmail,
            'name' => $toName
            ];

            if ($emailTemplate && $toEmail && $toName) {
                 $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                     ->setTemplateVars($variables)
                     ->setFrom($sender)
                     ->addTo($variables['recipient_email'])
                     ->setReplyTo($variables['recipient_email'], $variables['recipient_name'])
                     ->getTransport();
                 $transport->sendMessage();
                 $response = $this->resultFactory
                     ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                     ->setData(
                         [
                         'status' => 1,
                         'message' => 'Your message has been sent successfully.'
                         ]
                     );
                 return $response;
            } else {
                $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(
                    [
                        'status'  => 0,
                        'message' => "Something went wrong. Please try again."
                    ]
                );
                return $response;
            }
        } catch (\Exception $e) {
            $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData(
                [
                 'status'  => 0,
                 'message' => $e->getMessage()
                ]
            );
            return $response;
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
