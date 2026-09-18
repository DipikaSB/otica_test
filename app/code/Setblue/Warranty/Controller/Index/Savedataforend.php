<?php

namespace Setblue\Warranty\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Setblue\Warranty\Model\WarrantyFactory;

class Savedataforend extends Action
{
    protected $warrantyFactory;
    protected $resultFactory;
    protected $directory;
    protected $uploaderFactory;
    protected $transportBuilder;
    protected $scopeConfig;
    protected $storeManager;

    public function __construct(
        Context $context,
        WarrantyFactory $warrantyFactory,
        ResultFactory $resultFactory,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->warrantyFactory  = $warrantyFactory;
        $this->resultFactory    = $resultFactory;
        $this->directory        = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory  = $uploaderFactory;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig      = $scopeConfig;
        $this->storeManager     = $storeManager;
    }

    public function execute()
    {
        try {
            date_default_timezone_set("Asia/Kolkata");

            $data = [
                'fname'         => $this->getRequest()->getParam('fname'),
                'lname'         => $this->getRequest()->getParam('lname'),
                'phone_no'      => $this->getRequest()->getParam('phone'),
                'email'         => $this->getRequest()->getParam('email_id'),
                'product_name'  => $this->getRequest()->getParam('product_name'),
                'serial_no'     => $this->getRequest()->getParam('serial_no'),
                'purchase_date' => $this->getRequest()->getParam('purchase_date'),
                'created_date'  => date("Y-m-d H:i:s")
            ];

            /** ------------------------------
             *  FILE UPLOAD
             * ------------------------------ */
            $file = $this->getRequest()->getFiles('purchase_receipt');

            if ($file && isset($file['name']) && $file['name'] !== '') {

                $uploader = $this->uploaderFactory->create(['fileId' => 'purchase_receipt']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                $uploader->setAllowRenameFiles(true);

                $mediaPath = 'warranty/';
                $target    = $this->directory->getAbsolutePath($mediaPath);

                $result = $uploader->save($target);

                if (!$result) {
                    throw new \Exception('File cannot be saved.');
                }

                $data['purchase_receipt'] = $mediaPath . $result['file'];
            }

            /** ------------------------------
             *  SAVE DATA
             * ------------------------------ */
            $model = $this->warrantyFactory->create();
            $model->setData($data);
            $model->save();

            /** ------------------------------
             *  SEND EMAIL
             * ------------------------------ */
            $this->sendMail($data);

            $this->sendMailToAdmin($data);

            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'status'  => 1,
                'message' => 'Warranty submitted successfully'
            ]);

        } catch (\Exception $e) {

            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'status'  => 0,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function sendMail($data)
    {
        $senderIdentity = $this->scopeConfig->getValue(
            'Warranty/general/email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $templateId = $this->scopeConfig->getValue(
            'Warranty/general/cls_email_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // Extract sender email/name
        $sender = [
            'name'  => $this->scopeConfig->getValue(
                'trans_email/ident_' . $senderIdentity . '/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'email' => $this->scopeConfig->getValue(
                'trans_email/ident_' . $senderIdentity . '/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ];

        $customerEmail = $data['email'];
        $adminEmail    = $sender['email'];

        $templateVars = [
            'fname' => $data['fname'],
            'lname' => $data['lname'],
            'phone_no' => $data['phone_no'],
            'email' => $data['email'],
            'product_name' => $data['product_name'],
            'serial_no' => $data['serial_no'],
            'purchase_date' => $data['purchase_date']
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($customerEmail)
            ->addBcc($adminEmail)
            ->getTransport();

        $transport->sendMessage();
    }

    private function sendMailToAdmin($data)
    {
        $senderIdentity = $this->scopeConfig->getValue(
            'Warranty/general/email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $templateId = 'Warranty_general_cls_email_template_to_admin';

        // Extract sender email/name
        $sender = [
            'name'  => $this->scopeConfig->getValue(
                'trans_email/ident_' . $senderIdentity . '/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'email' => $this->scopeConfig->getValue(
                'trans_email/ident_' . $senderIdentity . '/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ];

        $adminEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if (!$adminEmail || trim($adminEmail) === '') {
            $adminEmail = 'support@oticagroup.com'; // fallback static email
        }

        $templateVars = [
            'fname' => $data['fname'],
            'lname' => $data['lname'],
            'phone_no' => $data['phone_no'],
            'email' => $data['email'],
            'product_name' => $data['product_name'],
            'serial_no' => $data['serial_no'],
            'purchase_date' => $data['purchase_date'],
            'purchase_receipt' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $data['purchase_receipt']
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($adminEmail)
            ->getTransport();

        $transport->sendMessage();
    }

}
