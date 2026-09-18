<?php
namespace Phonepe\PG\Service;

use PhonePe\Env;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Phonepe\PG\Helper\Data;

class SdkClientFactory
{
    protected $dataHelper;
    protected $client = null;

    public function __construct(Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    private function getPhonePeEnv(): string
    {
        $magentoEnv = $this->dataHelper->getEnvironment();

        switch ($magentoEnv) {
            case 'production':
                return Env::PRODUCTION;
            case 'uat':
                return Env::UAT;
            case 'stage':
            default:
                return Env::STAGE;
        }
    }

    public function getClient(): StandardCheckoutClient
    {
        if ($this->client === null) {
            $env        = $this->dataHelper->getEnvironment();
            $phonePeEnv = $this->getPhonePeEnv();

            $shouldPublishEvents = true;

            $this->client = StandardCheckoutClient::getInstance(
                $this->dataHelper->getClientId(),
                (int) $this->dataHelper->getClientVersion(),
                $this->dataHelper->getClientSecret(),
                $phonePeEnv,
                $shouldPublishEvents
            );
        }

        return $this->client;
    }

    public function createClient(string $clientId, string $clientSecret): StandardCheckoutClient
    {
        $env        = $this->dataHelper->getEnvironment();
        $phonePeEnv = $this->getPhonePeEnv();
        $version    = (int) $this->dataHelper->getClientVersion();

        $shouldPublishEvents = true;

        return StandardCheckoutClient::getInstance(
            $clientId,
            $version,
            $clientSecret,
            $phonePeEnv,
            $shouldPublishEvents
        );
    }
}
