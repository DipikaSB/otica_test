<?php

namespace Setblue\AllEmailCcBcc\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH = 'setblue_allemailccbcc/general/';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Get config value
     */
    public function getConfig($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check module enabled
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig('enabled', $storeId);
    }

    /**
     * Get CC emails
     */
    public function getCcTo($storeId = null)
    {
        return $this->validateEmails(
            $this->getEmailList($this->getConfig('cc_email', $storeId))
        );
    }

    /**
     * Get BCC emails
     */
    public function getBccTo($storeId = null)
    {
        return $this->validateEmails(
            $this->getEmailList($this->getConfig('bcc_email', $storeId))
        );
    }

    /**
     * Convert string to email array
     */
    private function getEmailList($emails)
    {
        if (!$emails) {
            return [];
        }

        return array_map('trim', explode(',', $emails));
    }

    /**
     * Validate email list
     */
    private function validateEmails(array $emails)
    {
        return array_values(
            array_filter($emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            })
        );
    }

    /**
     * Get current store ID
     */
    public function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}