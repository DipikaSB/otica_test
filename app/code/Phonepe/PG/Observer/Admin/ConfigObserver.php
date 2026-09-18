<?php
namespace Phonepe\PG\Observer\Admin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Phonepe\PG\Model\Constants\EventConstants;
use Phonepe\PG\Service\EventService;
use Phonepe\PG\Service\WebhookRegistrar;
use Psr\Log\LoggerInterface;

class ConfigObserver implements ObserverInterface
{
    protected $request;
    protected $webhookRegistrar;
    protected $messageManager;
    protected $logger;
    protected $scopeConfig;
    protected $encryptor;
    protected $eventService;

    public function __construct(
        RequestInterface $request,
        WebhookRegistrar $webhookRegistrar,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        EventService $eventService,
    ) {
        $this->request          = $request;
        $this->webhookRegistrar = $webhookRegistrar;
        $this->messageManager   = $messageManager;
        $this->logger           = $logger;
        $this->scopeConfig      = $scopeConfig;
        $this->eventService     = $eventService;
        $this->encryptor        = $encryptor;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('[PhonePe Observer] Admin config save event triggered.');

        $changedPaths = $observer->getEvent()->getChangedPaths();
        $oldIsActive  = $this->scopeConfig->getValue('payment/phonepe_pg/active');

        if ($oldIsActive == 0) {
            $this->logger->info('[PhonePe Observer] Plugin deactivated event triggered.');
            $this->eventService->sendEvent(EventConstants::PLUGIN_DEACTIVATED);
        }

        $clientIdPath     = 'payment/phonepe_pg/client_id';
        $clientSecretPath = 'payment/phonepe_pg/client_secret';

        $clientIdChanged     = in_array($clientIdPath, $changedPaths);
        $clientSecretChanged = in_array($clientSecretPath, $changedPaths);

        if ($clientIdChanged && $clientSecretChanged) {
            $clientId        = $this->scopeConfig->getValue($clientIdPath);
            $encryptedSecret = $this->scopeConfig->getValue($clientSecretPath);
            $clientSecret    = $encryptedSecret ? $this->encryptor->decrypt($encryptedSecret) : null;

            if (! $clientId || ! $clientSecret) {
                $this->messageManager->addErrorMessage(__('Client ID or Client Secret is missing. Webhook registration cannot proceed.'));
                return;
            }

            try {
                $result = $this->webhookRegistrar->registerWebhook($clientId, $clientSecret);
                if ($result['success']) {
                    $this->eventService->sendEvent(EventConstants::CHANGES_SAVED_AND_PLUGIN_ACTIVATED);
                    $this->messageManager->addSuccessMessage($result['message']);
                } else {
                    $this->messageManager->addErrorMessage($result['message']);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Webhook registration failed: %1', $e->getMessage()));
                $this->logger->error('[PhonePe Observer] Webhook registration exception: ' . $e->getMessage());
            }
        } else {
            $this->logger->info('[PhonePe Observer] No PhonePe credential changes detected. Skipping webhook registration.');
        }
    }
}
