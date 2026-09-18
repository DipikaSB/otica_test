<?php

namespace Meetanshi\ReviewReminderBasic\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public const ENABLE = 'admin_reviewreminderbasic/config/enable';
    public const DAYS = 'admin_reviewreminderbasic/config/days';
    public const SENDER = 'admin_reviewreminderbasic/config/sender';
    public const EMAIL_TEMPLATE = 'admin_reviewreminder_config_general_email_template';
    public const ORDER_STATUS = 'admin_reviewreminderbasic/config/order_status';

    public const THANKYOU_ENABLE = 'admin_reviewreminderbasic/thankyou/enable';
    public const THANKYOU_COUPON_CODE = 'admin_reviewreminderbasic/thankyou/coupon_code';
    public const THANKYOU_SENDER = 'admin_reviewreminderbasic/thankyou/thankyou_sender';
    public const THANKYOU_EMAIL_TEMPLATE = 'admin_reviewreminderbasic/thankyou/thankyou_email_template';
    public const THANKYOU_DEFAULT_TEMPLATE = 'admin_reviewreminderbasic_thankyou_thankyou_email_template';
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param Repository $assetRepo
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        Repository $assetRepo,
        TransportBuilder $transportBuilder
    ) {
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->assetRepo = $assetRepo;
        parent::__construct($context);
    }

    /**
     * Check if module is enabled.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @param mixed $storeId
     *
     * @return bool
     */
    public function getConfig(mixed $storeId = null)
    {
        try {
            $scope = ScopeInterface::SCOPE_STORE;
            if ($this->scopeConfig->getValue(self::ENABLE, $scope, $storeId)):
                return true;
            else:
                return false;
            endif;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get reminder days configuration.
     *
     * @param mixed $storeId
     *
     * @return mixed
     */
    public function getDays(mixed $storeId = null)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::DAYS, $scope, $storeId);
    }

    /**
     * Get configured order statuses for review reminder.
     *
     * @param mixed $storeId
     *
     * @return array
     */
    public function getOrderStatuses(mixed $storeId = null)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        $value = $this->scopeConfig->getValue(self::ORDER_STATUS, $scope, $storeId);
        if ($value) {
            return explode(',', $value);
        }
        return ['complete'];
    }

    /**
     * Get current time.
     *
     * @return string
     */
    public function getCurrentTime()
    {
        try {
            return $this->timezone->date()->format('Y-m-d H:i:s');

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Send review reminder email.
     *
     * @param mixed $config
     *
     * @return $this|string
     */
    public function sendReviewReminderMail(mixed $config)
    {
        try {
            $config['template'] = self::EMAIL_TEMPLATE;
            $config['storename'] = $this->getStoreName($config['storeId'] ?? null);
            $config['image'] = $this->assetRepo->getUrl('Meetanshi_ReviewReminderBasic::images/reminder.jpg');
            $this->inlineTranslation->suspend();
            $this->generateTemplate($config);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return $this;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get store name.
     *
     * @return mixed
     */
    public function getStoreName($storeId = null)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            'general/store_information/name',
            $scope,
            $storeId
        );
    }

    /**
     * Generate email template.
     *
     * @param mixed $config
     *
     * @return $this|string
     */
    public function generateTemplate(mixed $config)
    {
        try {
            $storeId = $config['storeId'] ?? $this->storeManager->getStore()->getId();
            $this->transportBuilder->setTemplateIdentifier($config['template'])
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars($config)
                ->setFrom($this->scopeConfig->getValue(self::SENDER, ScopeInterface::SCOPE_STORE, $storeId))
                ->addTo($config['mail']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $this;
    }

    /**
     * Check if Thank You email is enabled.
     *
     * @param mixed $storeId
     *
     * @return bool
     */
    public function isThankYouEmailEnabled(mixed $storeId = null)
    {
        try {
            return (bool) $this->scopeConfig->getValue(
                self::THANKYOU_ENABLE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the coupon code for Thank You email.
     *
     * @param mixed $storeId
     *
     * @return string
     */
    public function getThankYouCouponCode(mixed $storeId = null)
    {
        return (string) $this->scopeConfig->getValue(
            self::THANKYOU_COUPON_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the email sender for Thank You email.
     *
     * @param mixed $storeId
     *
     * @return mixed
     */
    public function getThankYouSender(mixed $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::THANKYOU_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the email template identifier for Thank You email.
     *
     * @param mixed $storeId
     *
     * @return string
     */
    public function getThankYouEmailTemplate(mixed $storeId = null)
    {
        $template = $this->scopeConfig->getValue(
            self::THANKYOU_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $template ?: self::THANKYOU_DEFAULT_TEMPLATE;
    }

    /**
     * Send Thank You email with coupon code after review submission.
     *
     * @param array $config Must include: customer_name, customer_email, product_name
     *
     * @return $this|string
     */
    public function sendThankYouEmail(array $config)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $templateId = $this->getThankYouEmailTemplate($storeId);
            $sender = $this->getThankYouSender($storeId);
            $couponCode = $this->getThankYouCouponCode($storeId);

            $templateVars = [
                'customer_name' => $config['customer_name'] ?? '',
                'product_name'  => $config['product_name'] ?? '',
                'coupon_code'   => $couponCode,
                'storename'     => $this->getStoreName(),
            ];

            $this->inlineTranslation->suspend();

            $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($config['customer_email']);

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return $this;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
