<?php
namespace Setblue\OpenAIFeed\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Psr\Log\LoggerInterface;

class UpdateCronSchedule implements ObserverInterface
{
    const XML_PATH_CRON_EXPR = 'crontab/default/jobs/openai_feed_generate/schedule/cron_expr';
    const XML_PATH_CRON_CUSTOM = 'openai_feed/settings/cron_schedule';

    protected $scopeConfig;
    protected $configWriter;
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $cronExpr = $this->scopeConfig->getValue(self::XML_PATH_CRON_CUSTOM);
            if (!$cronExpr) {
                $cronExpr = '*/30 * * * *'; // default fallback
            }

            $this->configWriter->save(self::XML_PATH_CRON_EXPR, $cronExpr);
            $this->logger->info('OpenAI Feed Cron schedule updated to: ' . $cronExpr);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update OpenAI Feed cron schedule: ' . $e->getMessage());
        }
    }
}
