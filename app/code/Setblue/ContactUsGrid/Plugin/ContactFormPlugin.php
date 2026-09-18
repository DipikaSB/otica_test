<?php

namespace Setblue\ContactUsGrid\Plugin;

use Magento\Contact\Model\Contact;
use Magento\Framework\DataObject\Factory;
use Setblue\ContactUsGrid\Model\ContactUsGridFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Contact\Controller\Index\Post;
use Magento\Store\Model\ScopeInterface;

class ContactFormPlugin
{
    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var ContactUsGridFactory
     */
    private $contactFormFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ContactFormPlugin constructor.
     *
     * @param Factory $dataObjectFactory
     * @param ContactUsGridFactory $contactFormFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Factory $dataObjectFactory,
        ContactUsGridFactory $contactFormFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->contactFormFactory = $contactFormFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Plugin method executed after the original execute method in Magento\Contact\Controller\Index\Post.
     *
     * @param ContactPostController $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(
        Post $subject,
        $result
    ) {
        if ($this->scopeConfig->isSetFlag(
            'contactform/settings/enable',
            ScopeInterface::SCOPE_STORE
        )) {

            $this->saveContactFormData($subject->getRequest()->getPostValue());
        }
        return $result;
    }

    /**
     * Save contact form data to the custom table.
     *
     * @param array $postData
     */
    private function saveContactFormData($postData)
    {

        $model = $this->contactFormFactory->create();

        $currentStore = $this->storeManager->getStore();
        $storeId = $currentStore->getId();

        $model->setData($postData);
        $model->setStoreId($storeId);
        $model->save();
    }
}
