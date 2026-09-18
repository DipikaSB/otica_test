<?php
namespace Phonepe\PG\Service;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Math\Random;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Phonepe\PG\Helper\Data as PhonepeHelper;
use Phonepe\PG\Model\Constants\EndpointConstants;
use Phonepe\PG\Model\Constants\StatusConstants;
use Phonepe\PG\Service\SdkClientFactory;
use Psr\Log\LoggerInterface;

class WebhookRegistrar
{
    protected $helper;
    protected $sdkClientFactory;
    protected $curl;
    protected $random;
    protected $configWriter;
    protected $logger;
    protected $moduleList;
    protected $productMetadata;
    protected $storeManager;

    public function __construct(
        PhonepeHelper $helper,
        SdkClientFactory $sdkClientFactory,
        Curl $curl,
        Random $random,
        Config $configWriter,
        LoggerInterface $logger,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager
    ) {
        $this->helper           = $helper;
        $this->sdkClientFactory = $sdkClientFactory;
        $this->curl             = $curl;
        $this->random           = $random;
        $this->configWriter     = $configWriter;
        $this->logger           = $logger;
        $this->moduleList       = $moduleList;
        $this->productMetadata  = $productMetadata;
        $this->storeManager     = $storeManager;
    }

    /**
     * Registers the webhook with PhonePe and saves the generated credentials.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return array ['success' => bool, 'message' => string]
     */

    private function generateUsername($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new \Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    private function generatePassword($length = 20)
    {
        $length = min(max($length, 8), 20);

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';

        $password = $letters[random_int(0, strlen($letters) - 1)] . $numbers[random_int(0, strlen($numbers) - 1)];

        $allChars = $letters . $numbers;
        for ($i = 2; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        $password = str_shuffle($password);

        return $password;
    }

    public function registerWebhook($clientId, $clientSecret)
    {
        try {
            $sdkClient   = $this->sdkClientFactory->createClient($clientId, $clientSecret);
            $oauthToken  = $sdkClient->getAuthHeadersToken();
            $callbackUrl = $this->helper->getWebhookUrl();

            $username = $this->generateUsername(12);
            $password = $this->generatePassword(12);

            if ($this->helper->getEnvironment() === 'production') {
                $webhookApiUrl = EndpointConstants::PROD_WEBHOOK_URL;
            } else {
                $webhookApiUrl = EndpointConstants::UAT_WEBHOOK_URL;
            }

            $payload = [
                "webhooks" => [
                    [
                        "channel" => [
                            "type"        => "HTTPS",
                            "url"         => $callbackUrl,
                            "username"    => $username,
                            "password"    => $password,
                            "description" => "Magento 2 Plugin Webhook",
                        ],
                        "events"  => [StatusConstants::WEBHOOK_PAYMENT_SUCCESS, StatusConstants::WEBHOOK_PAYMENT_FAILURE],
                    ],
                ],
            ];

            $headers = [
                'Authorization'             => $oauthToken,
                'Content-Type'              => 'application/json',
                'Accept'                    => 'application/json',
                'X-SOURCE'                  => 'MAGENTO_PLUGIN',
                'X-SOURCE-VERSION'          => $this->moduleList->getOne('Phonepe_PG')['setup_version'] ?? '1.0.0',
                'X-SOURCE-PLATFORM'         => 'Magento',
                'X-SOURCE-PLATFORM-VERSION' => $this->productMetadata->getVersion(),
                'X-MERCHANT-DOMAIN'         => $this->storeManager->getStore()->getBaseUrl(),
            ];

            $this->curl->setHeaders($headers);

            try {
                $this->curl->post($webhookApiUrl, json_encode($payload));
            } catch (\Exception $exception) {
                $this->logger->error('[PhonePe Webhook] Error while posting API ' . $exception);
            }

            $response = json_decode($this->curl->getBody(), true);

            if (isset($response['code']) && $response['code'] === 'SUCCESS') {

                $this->configWriter->saveConfig('payment/phonepe_pg/callback_username', $username, 'default', 0);
                $this->configWriter->saveConfig('payment/phonepe_pg/callback_password', $password, 'default', 0);

                $this->logger->info('[PhonePe Webhook] Registration successful. Credentials saved.');
                return ['success' => true, 'message' => __('PhonePe webhook registered successfully.')];
            } else {
                $errorMessage = $response['message'] ?? 'Unknown error during webhook registration.';
                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            $this->logger->error('[PhonePe Webhook] Registration failed: ' . $e->getMessage());
            return ['success' => false, 'message' => __('PhonePe webhook registration failed.')];
        }
    }
}
