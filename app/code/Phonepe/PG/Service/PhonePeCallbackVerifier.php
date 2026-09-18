<?php
namespace Phonepe\PG\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use PhonePe\common\exceptions\PhonePeException;
use Phonepe\PG\Model\Constants\EventConstants;
use Phonepe\PG\Service\EventService;
use Phonepe\PG\Service\SdkClientFactory;

class PhonePeCallbackVerifier
{
    const XML_PATH_CALLBACK_USERNAME = 'payment/phonepe_pg/callback_username';
    const XML_PATH_CALLBACK_PASSWORD = 'payment/phonepe_pg/callback_password';

    protected $scopeConfig;
    protected $sdkClient;
    protected $request;
    protected $encryptor;
    protected $eventService;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SdkClientFactory $sdkClientFactory,
        HttpRequest $request,
        EncryptorInterface $encryptor,
        EventService $eventService
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->sdkClient    = $sdkClientFactory->getClient();
        $this->request      = $request;
        $this->encryptor    = $encryptor;
        $this->eventService = $eventService;
    }

    public function verify(array $headers, string $rawBody): array
    {
        $callbackUsername  = $this->scopeConfig->getValue(self::XML_PATH_CALLBACK_USERNAME);
        $encryptedPassword = $this->scopeConfig->getValue(self::XML_PATH_CALLBACK_PASSWORD);

        if (empty($callbackUsername) || empty($encryptedPassword)) {
            throw new LocalizedException(__('Callback credentials are not configured in the Magento admin panel.'));
        }

        $callbackPassword = $this->encryptor->decrypt($encryptedPassword);

        try {
            $decodedBody = json_decode($rawBody, true);

            $this->eventService->sendEvent(EventConstants::CALLBACK_RECIEVED_AT_PLUGIN);

            return $this->sdkClient->verifyCallbackResponse(
                $headers,
                $decodedBody,
                $callbackUsername,
                $callbackPassword
            );
        } catch (PhonePeException $e) {
            throw new LocalizedException(__('PhonePe callback verification failed: ' . $e->getMessage()));
        }
    }
}
