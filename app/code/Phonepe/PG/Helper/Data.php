<?php
namespace Phonepe\PG\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Phonepe\PG\Model\Constants\EndpointConstants;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_PHONEPE = 'payment/phonepe_pg/';

    protected $logger;
    protected $encryptor;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->logger    = $logger;
        $this->encryptor = $encryptor;
    }

    /**
     * Get config value from store settings
     */
    public function getConfig($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PHONEPE . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getClientId()
    {
        return $this->getConfig('client_id');
    }

    public function getClientSecret()
    {
        $encryptedSecret = $this->getConfig('client_secret');
        return $this->encryptor->decrypt($encryptedSecret);
    }

    public function getClientVersion()
    {
        return $this->getConfig('client_version');
    }

    public function getEnvironment()
    {
        return $this->getConfig('environment');
    }

    public function getRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('phonepe/index/callback', ['_secure' => true]);
    }

    public function getWebhookUrl()
    {
        return $this->_urlBuilder->getUrl('phonepe/index/webhook', ['_secure' => true]);
    }

    public function generateMerchantTransactionId($incrementId)
    {
        return $incrementId;
    }

    public function getDashboardUrl(): string
    {
        switch ($this->getEnvironment()) {
            case 'production':
                return EndpointConstants::PROD_DASHBOARD_URL;
            case 'uat':
                return EndpointConstants::UAT_DASHBOARD_URL;
            case 'stage':
            default:
                return EndpointConstants::STAGE_DASHBOARD_URL;
        }
    }

    public function getCheckoutJsUrl()
    {
        switch ($this->getEnvironment()) {
            case 'production':
                return EndpointConstants::PROD_CHECKOUT_JS_URL;
            case 'uat':
                return EndpointConstants::UAT_CHECKOUT_JS_URL;
            case 'stage':
            default:
                return EndpointConstants::STAGE_CHECKOUT_JS_URL;
        }
    }

    public function getDisplayMode()
    {
        return $this->getConfig('display_mode');
    }

    /**
     * Log informational messages
     */
    public function logInfo($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }
}
