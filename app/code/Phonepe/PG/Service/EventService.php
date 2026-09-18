<?php
namespace Phonepe\PG\Service;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PhonePe\common\eventHandler\Event;
use PhonePe\common\exceptions\PhonePeException;
use Phonepe\PG\Helper\Data as PhonepeHelper;
use Phonepe\PG\Service\SdkClientFactory;
use Psr\Log\LoggerInterface;

class EventService
{
    // A constant for non-transactional events, as seen in the WooCommerce example
    private const NON_TRANSACTIONAL_EVENT = 'NON_TRANSACTIONAL_EVENT';

    protected $helper;
    protected $sdkClientFactory;
    protected $logger;
    protected $moduleList;
    protected $productMetadata;
    protected $date;

    public function __construct(
        PhonepeHelper $helper,
        SdkClientFactory $sdkClientFactory,
        LoggerInterface $logger,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
        DateTime $date
    ) {
        $this->helper           = $helper;
        $this->sdkClientFactory = $sdkClientFactory;
        $this->logger           = $logger;
        $this->moduleList       = $moduleList;
        $this->productMetadata  = $productMetadata;
        $this->date             = $date;
    }

    /**
     * Creates and sends an event using the PhonePe PHP SDK.
     *
     * @param string $eventName The name of the event (e.g., from EventConstants).
     * @param array $eventData Additional contextual data for the event (e.g., ['amount' => 1000, 'state' => 'SUCCESS']).
     * @param string|null $merchantOrderId The merchant order ID. If null, it's treated as a non-transactional event.
     * @return void
     */
    public function sendEvent(string $eventName, array $eventData = [], ?string $merchantOrderId = null)
    {
        try {
            $event = new Event();

            $baseEventData = [
                'platform'            => 'Magento',
                'platformVersion'     => $this->productMetadata->getVersion(),
                'pluginVersion'       => $this->moduleList->getOne('Phonepe_PG')['setup_version'] ?? 'N/A',
                'flowType'            => 'B2B_PG',
                'userOperatingSystem' => isset($_SERVER['HTTP_USER_AGENT']) ? htmlspecialchars($_SERVER['HTTP_USER_AGENT']) : 'N/A',
            ];

            $finalEventData = array_merge($baseEventData, $eventData);

            $dateTime = (string) round(microtime(true) * 1000);
            $event->setEventName($eventName);
            $event->setEventTime($dateTime);
            $event->setData($finalEventData);

            if ($merchantOrderId) {
                $event->setMerchantOrderId($merchantOrderId);
            } else {
                $event->setMerchantOrderId(self::NON_TRANSACTIONAL_EVENT);
            }

            $client = $this->sdkClientFactory->getClient();

            $client->sendEvent($event);

            $this->logger->info('[PhonePe Events] Successfully sent event via SDK.', ['eventName' => $eventName]);

        } catch (PhonePeException $e) {
            $this->logger->error('[PhonePe Events] A PhonePe SDK exception occurred while sending an event.', [
                'eventName' => $eventName,
                'error'     => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[PhonePe Events] A general exception occurred while sending an event.', [
                'eventName' => $eventName,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
