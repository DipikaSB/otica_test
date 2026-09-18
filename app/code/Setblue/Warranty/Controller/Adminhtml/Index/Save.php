<?php

namespace Setblue\Warranty\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\Mail\Template\TransportBuilder;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     */
    protected $scopeConfig;
    protected $transportBuilder;
    protected $_fileUploaderFactory;
    protected $_file;
    protected $_storeManager;
    protected $_filesystem;
    protected $_scopeConfig;

    public function __construct(
        Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Driver\File $file,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->_filesystem = $filesystem;
        $this->_file = $file;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->_objectManager->create(\Setblue\Warranty\Model\Warranty::class);
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            $magentoDateObject = $this->_objectManager
            ->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
            $magentoDate = $magentoDateObject->gmtDate();
            // $model->setData('view', $data['view']);
            try {
                $model->save();
                $post = $this->getRequest()->getPostValue(); // send mail code
                $senderName = $this->_scopeConfig->getValue(
                    'trans_email/ident_general/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $senderEmail = $this->_scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $customerEmail = $post['email'];

                $postObject = new \Magento\Framework\DataObject();
                $templateVars = [
                    'view' => "view test Static",
                    'customer_name' => $post['fname'],
                ];

                $sender = [
                    'name' => $senderName,
                    'email' => $senderEmail,
                ];
                $transport = $this->transportBuilder->setTemplateIdentifier('reply_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($customerEmail)
                ->setReplyTo($senderEmail)
                ->getTransport();
                $transport->sendMessage();

                $this->messageManager->addSuccess(__('The Quotation Reply For Request Save Successfully.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                
                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the entry.'));
            }

            $this->_getSession()->setFormData($data);
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
