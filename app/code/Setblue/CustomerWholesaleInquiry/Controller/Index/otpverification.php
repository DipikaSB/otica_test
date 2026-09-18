<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Setblue\CustomerWholesaleInquiry\Model\OtpVerficationFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_ShareCart
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class otpverification extends \Magento\Framework\App\Action\Action
{
    
    private $data;
    public $JsonFactory;
    protected $_OtpVerficationFactory;
    protected $_customerSession;
    protected $resourceConnection;

    /**
     * Index constructor.
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param Session $session
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param Data $data
     * @param ResultFactory $resultFactory
     * @param EncoderInterface $urlEncoder
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        JsonFactory $JsonFactory,
        OtpVerficationFactory $OtpVerficationFactory,
        \Magento\Customer\Model\Session $customerSession,
        ResourceConnection $resourceConnection
    ) {
        
        $this->resultFactory = $resultFactory;
        $this->_OtpVerficationFactory = $OtpVerficationFactory;
        $this->JsonFactory = $JsonFactory;
        $this->_customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $result = $this->JsonFactory->create();
        if ($this->_customerSession->isLoggedIn()) {
            $email_id =  $this->_customerSession->getCustomer()->getEmail();
        } else {
            $email_id = $this->getRequest()->getParam('email_id');
        }
        $postotp = $this->getRequest()->getParam('postotp');
        

        $verification = $this->_OtpVerficationFactory->create();
        $collectioncat = $verification->getCollection()->addFieldToFilter('email', $email_id)->setOrder('otp_id', 'DESC');
        $i=1;
        $OTP='';
        foreach ($collectioncat as $datacollection) {
            if ($i=='1') {
                $OTP = $datacollection->getOtp();
                //return $result->setData($OTP);
            }
            $i++;
        }
        $yes='Yes';
        $no='No';
        if (strcmp($postotp, $OTP) == 0) {
            $connection = $this->resourceConnection->getConnection();
            $is_verified = 'Verified';
            $sqltheir = "update Sns_Otp_Verfication set is_verified='".$is_verified."' where otp = '".$postotp."'";
            $connection->query($sqltheir);
            return $result->setData($yes);
        } else {
            return $result->setData($no);
        }
    }
}
