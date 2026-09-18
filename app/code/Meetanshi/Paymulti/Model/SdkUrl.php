<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Meetanshi\Paymulti\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Paypal\Model as paypalModel;
use Magento\Paypal\Model\ConfigFactory;

/**
 * Provides URL to PP SDK based on configuration
 */
class SdkUrl extends \Magento\Paypal\Model\SdkUrl
{
    /**
     * Base url for Paypal SDK
     */
    private const BASE_URL = 'https://www.paypal.com/sdk/js?';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * Maps the old checkout SDK configuration values to the current ones
     *
     * @var array
     */
    private $disallowedFundingMap;

    /**
     * These payment methods will be added as parameters to the SDK url to disable them.
     *
     * @var array
     */
    private $unsupportedPaymentMethods;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * Generated Url to PayPAl SDK
     *
     * @var string
     */
    private $url;

    /**
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param array $disallowedFundingMap
     * @param array $unsupportedPaymentMethods
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        $disallowedFundingMap = [],
        $unsupportedPaymentMethods = []
    )
    {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(paypalModel\Config::METHOD_EXPRESS);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->disallowedFundingMap = $disallowedFundingMap;
        $this->unsupportedPaymentMethods = $unsupportedPaymentMethods;
    }

    /**
     * Generate the url to download the Paypal SDK
     *
     * @return string
     */
    public function getUrl(): string
    {
        if (empty($this->url)) {
            $components = [];
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $parypalHelper = $om->get('Meetanshi\Paymulti\Helper\Data');
            $setCurrency = $parypalHelper->isActive();
            $currncy = $this->storeManager->getStore()->getBaseCurrencyCode();
            if ($setCurrency) {
                if ($parypalHelper->getCheckoutWith() == 0) {
                    $currncy = $this->storeManager->getStore()->getCurrentCurrencyCode();
                } else {
                    $currncy = $parypalHelper->getToCheckout();
                }
            }

            $params = [
                'client-id' => $this->getClientId(),
                'locale' => $this->localeResolver->getLocale(),
//                'currency' => $this->storeManager->getStore()->getBaseCurrencyCode(),
                'currency' => $currncy,
            ];

            if ($this->areMessagesEnabled()) {
                $components[] = 'messages';
            }
            if ($this->areButtonsEnabled()) {
                $components[] = 'buttons';
                $params['commit'] = 'false';
                $params['intent'] = $this->getIntent();
                $params['merchant-id'] = $this->config->getValue('merchant_id');
                $params['disable-funding'] = $this->getDisallowedFunding();
                $params = array_replace($params, $this->queryParams);
            }
            $params['components'] = implode(',', $components);
            $this->url = self::BASE_URL . http_build_query(array_filter($params));
        }
        return $this->url;
    }

    /**
     * Set query params in PayPal SDK Url
     *
     * @param string $key
     * @param string $value
     */
    public function setQueryParam(string $key, string $value)
    {
        $allowedParams = ['commit'];
        if (in_array($key, $allowedParams)) {
            $this->queryParams[$key] = $value;
        }
    }

    /**
     * Check if PP PayLater Messages enabled
     *
     * @return bool
     */
    private function areMessagesEnabled()
    {
        //ToDo read from config
        return true;
    }

    /**
     * Check if SmartButtons enabled
     *
     * @return bool
     */
    private function areButtonsEnabled()
    {
        return (bool)(int)$this->config->getValue('in_context');
    }

    /**
     * Get configured value for PayPal client id
     *
     * @return mixed
     */
    private function getClientId()
    {
        return (int)$this->config->getValue('sandbox_flag') ?
            $this->config->getValue('sandbox_client_id') :
            $this->config->getValue('client_id');
    }

    /**
     * Returns disallowed funding from configuration after updating values
     *
     * @return string
     */
    private function getDisallowedFunding()
    {
        $disallowedFunding = $this->config->getValue('disable_funding_options');
        $result = $disallowedFunding ? explode(',', $disallowedFunding) : [];

        // PayPal Guest Checkout Credit Card Icons only available when Guest Checkout option is enabled
        if ($this->isPaypalGuestCheckoutAllowed() === false && !in_array('CARD', $result)) {
            array_push($result, 'CARD');
        }

        // Map old configuration values to current ones
        $result = array_map(function ($oldValue) {
            return $this->disallowedFundingMap[$oldValue] ?? $oldValue;
        }, $result);

        //disable unsupported payment methods
        $result = array_combine($result, $result);
        $result = array_merge($result, $this->unsupportedPaymentMethods);

        return implode(',', $result);
    }

    /**
     * Returns if is allowed PayPal Guest Checkout.
     *
     * @return bool
     */
    private function isPaypalGuestCheckoutAllowed(): bool
    {
        return $this->config->getValue('solution_type') === paypalModel\Config::EC_SOLUTION_TYPE_SOLE;
    }

    /**
     * Return intent value from the configuration payment_action value
     *
     * @return string
     */
    private function getIntent(): string
    {
        $paymentAction = $this->config->getValue('paymentAction');
        $mappedIntentValues = [
            paypalModel\Config::PAYMENT_ACTION_AUTH => 'authorize',
            paypalModel\Config::PAYMENT_ACTION_SALE => 'capture',
            paypalModel\Config::PAYMENT_ACTION_ORDER => 'order'
        ];
        return $mappedIntentValues[$paymentAction];
    }
}
