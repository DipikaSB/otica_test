<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Helper;

use Magedelight\Ga4\Model\Config\Source\ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Ga4\Model\DataSaving;
use Magento\Cookie\Helper\Cookie;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as Catalog;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepo;
use Magedelight\Ga4\Model\Cache\Backend\Option;
use Magento\Framework\Registry;
use Magento\Framework\Escaper;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Ga4\Model\GtagFactory;
use Magento\Framework\UrlInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * General Configuration
     */
    protected const XML_PATH_GA4_ENABLE = 'magedelight_ga4/general/enable';
    protected const XML_PATH_GA4_GDPR_ENABLE = 'magedelight_ga4/general/gdpr_enable';
    protected const XML_PATH_COOKIE_RESTRICTION = 'web/cookie/cookie_restriction';
    /**
     * Ga4 GTM Code
     */
    protected const XML_PATH_GA4_TOP_JS = 'magedelight_ga4/gtm_code/ga4_top_js';
    /**
     * Google Ads Code
     */
    protected const XML_PATH_GA4_GAD_ENABLE_GA = 'magedelight_ga4/gad_code/enable_ga';
    protected const XML_PATH_GA4_GAD_TOP_JS = 'magedelight_ga4/gad_code/gad_top_js';
    protected const XML_PATH_GA4_GAD_REMARKETING = 'magedelight_ga4/gad_code/gad_remarketing';
    protected const XML_PATH_GA4_BOTTOM_JS = 'magedelight_ga4/gtm_code/ga4_bottom_js';
    protected const XML_PATH_GA4_GTM_CODE = 'magedelight_ga4/gtm_code/gtm_code_text';
    protected const XML_PATH_GA4_CONTAINER_ID = 'magedelight_ga4/gad_code/gad_container_id';
    protected const XML_PATH_GA4_LABEL_ID = 'magedelight_ga4/gad_code/gad_label_id';
    /**
     * Ga4 Product Configuration
     */
    protected const XML_PATH_GA4_IDENTIFIER = 'magedelight_ga4/product_configuration/identifier';
    protected const XML_PATH_GA4_CURRENCY = 'magedelight_ga4/product_configuration/currency';
    protected const XML_PATH_GA4_BRAND_STATUS = 'magedelight_ga4/product_configuration/brand_status';
    protected const XML_PATH_GA4_BRAND_ATTRIBUTE_CODE = 'magedelight_ga4/product_configuration/brand_attribute_code';
    protected const XML_PATH_GA4_PRODUCT_VARIANT = 'magedelight_ga4/product_configuration/product_variant';
    protected const XML_PATH_GA4_CONFIGURABLEITEMS = 'magedelight_ga4/product_configuration/configurableitems';
    protected const XML_PATH_GA4_CHECKOUT_PATH_HINT = 'magedelight_ga4/product_configuration/checkout_path_hint';
    protected const XML_PATH_GA4_ALLOWORDERSUMMARY = 'magedelight_ga4/product_configuration/allowordersummary';
    protected const XML_PATH_GA4_SUMMARY_TAX_EXCLUDE = 'magedelight_ga4/product_configuration/summary_tax_exclude';
    protected const XML_PATH_GA4_SUMMARY_SHIPPING_EXCLUDE=
    'magedelight_ga4/product_configuration/summary_shipping_exclude';
    protected const XML_PATH_GA4_DATALAYER = 'magedelight_ga4/product_configuration/ga4_datalayer_persistent_expiry';
    protected const XML_PATH_GA4_SECURE_COOKIES_SETTING='magedelight_ga4/product_configuration/secure_cookies_setting';
    protected const XML_PATH_GA4_ALLOW_NOT_LOGGEDIN_CUSTOMER=
    'magedelight_ga4/product_configuration/is_allow_not_logged_in_customer';

    /**
     * Ga4 Json Export Tags
     */
    protected const XML_PATH_GA4_JSON_EXPORT_PUBLIC_ID = 'magedelight_ga4/json_export/public_id';
    protected const XML_PATH_GA4_JSON_EXPORT_ENABLE_CRON = 'magedelight_ga4/cron_setting/enable_cron';
    protected const XML_PATH_GA4_JSON_EXPORT_CRON_TIME = 'magedelight_ga4/cron_setting/cron_time';

    protected const XML_PATH_GA4_STRICT_TRACKING = 'magedelight_ga4/general/strict_tracking';
    protected const XML_PATH_GA4_DOMAIN = 'magedelight_ga4/general/domain';
    protected const XML_PATH_GA4_GTM_EVENT = 'magedelight_ga4/gtm_event/event_list';

    protected const XML_PATH_GA4_PRODUCT_CUSTOM_OPTION = 'magedelight_ga4/data_exchange/custom_attribute_';

    /**
     * Ga4 real time tracking
     */
    public const XML_PATH_GA4_REALTIME_TRACKING = 'magedelight_ga4/realtime_tracking/enable_realtime_tracking';
    public const XML_PATH_GA4_SHOW_EMAIL = 'magedelight_ga4/realtime_tracking/show_user_email';

    public const XML_PATH_GA4_PROMOTION_TRACKING = 'magedelight_ga4/gtm_event/promotion_tracking';

    public const XML_DATABASE_SAVE = 'magedelight_ga4/cron_setting/db_save';

    /**
     * @var \Magedelight\Ga4\Model\DataSaving
     */
    protected $datasaving;

    /**
     * @var \Magento\Cookie\Helper\Cookie
     */
    protected $cookieHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GetStoreLevelCategories
     */
    protected $getStoreLevelCategories;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutsession;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $cacheState;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $Catalog;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepo;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Magedelight\Ga4\Model\GtagFactory
     */
    protected $gtagFactory;

    /**
     * @var array
     */
    protected $_brandOptions;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var StoreManagerInterface
     */
    protected $store;

    /**
     * @var BlockFactory
     */
    protected $createBlock;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var StateInterface
     */
    protected $stateInterface;

    /**
     * @var CacheInterface
     */
    protected $cacheInterface;

    /**
     * @var Catalog
     */
    protected $catalogCollection;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var Rule
     */
    protected $catalogRule;

    /**
     * @var CatalogRuleRepositoryInterface
     */
    protected $catalogRuleRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $_productloader;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeInterface
     * @param DataSaving $datasaving
     * @param Cookie $cookieHelper
     * @param BlockFactory $createBlock
     * @param ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutsession
     * @param CustomerSession $customerSession
     * @param StateInterface $stateInterface
     * @param CacheInterface $cacheInterface
     * @param Catalog $catalogCollection
     * @param OrderRepo $orderRepo
     * @param Registry $registry
     * @param Escaper $escaper
     * @param TimezoneInterface $timezoneInterface
     * @param QuoteFactory $quoteFactory
     * @param GtagFactory $gtagFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param Rule $catalogRule
     * @param CatalogRuleRepositoryInterface $catalogRuleRepository
     * @param UrlInterface $urlInterface
     * @param SerializerInterface $serializer
     * @param PriceCurrencyInterface $priceCurrency
     * @param LoggerInterface $logger
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeInterface,
        DataSaving $datasaving,
        Cookie $cookieHelper,
        BlockFactory $createBlock,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutsession,
        CustomerSession $customerSession,
        StateInterface $stateInterface,
        CacheInterface $cacheInterface,
        Catalog $catalogCollection,
        OrderRepo $orderRepo,
        Registry $registry,
        Escaper $escaper,
        TimezoneInterface $timezoneInterface,
        QuoteFactory $quoteFactory,
        GtagFactory $gtagFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        Rule $catalogRule,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        UrlInterface $urlInterface,
        SerializerInterface $serializer,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->store = $storeInterface;
        $this->datasaving = $datasaving;
        $this->cookieHelper = $cookieHelper;
        $this->createBlock = $createBlock;
        $this->checkoutsession = $checkoutsession;
        $this->customerSession = $customerSession;
        $this->stateInterface = $stateInterface;
        $this->cacheInterface = $cacheInterface;
        $this->catalogCollection = $catalogCollection;
        $this->orderRepo = $orderRepo;
        $this->_registry = $registry;
        $this->escaper = $escaper;
        $this->quoteFactory = $quoteFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->gtagFactory = $gtagFactory;
        $this->urlInterface = $urlInterface;
        $this->_httpContext = $httpContext;
        $this->cookieManager = $cookieManager;
        $this->catalogRule = $catalogRule;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->getStoreLevelCategories = [];
        $this->serializer = $serializer;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
        $this->_productloader = $_productloader;
    }

    /**
     * GetConfig
     *
     * @param string $config_path
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * IsGTMStatus
     */
    public function isGTMStatus()
    {
        $flag = false;
        if ($this->getConfig(self::XML_PATH_GA4_ENABLE)) {
            if ($this->getConfig(self::XML_PATH_GA4_STRICT_TRACKING)) {
                $baseUrl= $this->store->getStore()->getBaseUrl();
                $domains = $this->serializer->unserialize($this->getConfig(self::XML_PATH_GA4_DOMAIN));
                if ($domains) {
                    foreach ($domains as $domain) {
                        if (strpos($baseUrl, $domain['domain_name']) !== false) {
                            $flag = true;
                        }
                    }
                    if ($flag) {
                        return $this->getConfig(self::XML_PATH_GA4_ENABLE);
                    } else {
                        return 0;
                    }
                } else {
                    return $this->getConfig(self::XML_PATH_GA4_ENABLE);
                }
            } else {
                return $this->getConfig(self::XML_PATH_GA4_ENABLE);
            }
        } else {
            return $this->getConfig(self::XML_PATH_GA4_ENABLE);
        }
    }

    /**
     * GetGDPRStatus
     */
    public function getGDPRStatus()
    {
        return $this->getConfig(self::XML_PATH_COOKIE_RESTRICTION);
    }

    /**
     * GetModuleGdprStatus
     */
    public function getModuleGdprStatus()
    {
        return $this->getConfig(self::XML_PATH_GA4_GDPR_ENABLE);
    }

    /**
     * GetCookiesSetting
     */
    public function getCookiesSetting()
    {
        return $this->getConfig(self::XML_PATH_GA4_SECURE_COOKIES_SETTING);
    }

    /**
     * GetGTMJSCode
     */
    public function getGTMJSCode()
    {
        return $this->getConfig(self::XML_PATH_GA4_TOP_JS);
    }

    /**
     * GetGAContainerId
     */
    public function getGAContainerId()
    {
        return $this->getConfig(self::XML_PATH_GA4_CONTAINER_ID);
    }

    /**
     * Get Cron Status.
     *
     * @return mixed
     */
    public function getCronStatus()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GA4_JSON_EXPORT_ENABLE_CRON,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * GetCronTime
     */

    public function getCronTime()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GA4_JSON_EXPORT_CRON_TIME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * GetGALabelId
     */
    public function getGALabelId()
    {
        return $this->getConfig(self::XML_PATH_GA4_LABEL_ID);
    }

    /**
     * GetGTMNonJsCode
     */
    public function getGTMNonJsCode()
    {
        return trim($this->getConfig(self::XML_PATH_GA4_BOTTOM_JS));
    }

    /**
     * GetGTMCode
     */
    public function getGTMCode()
    {
        return trim($this->getConfig(self::XML_PATH_GA4_GTM_CODE));
    }

    /**
     * GetGADJSCode
     */
    public function getGADJSCode()
    {
        return $this->getConfig(self::XML_PATH_GA4_GAD_TOP_JS);
    }

    /**
     * GetGADRemarketing
     */
    public function getGADRemarketing()
    {
        return $this->getConfig(self::XML_PATH_GA4_GAD_REMARKETING);
    }

    /**
     * GetGaStatus
     */
    public function getGaStatus()
    {
        return $this->getConfig(self::XML_PATH_GA4_GAD_ENABLE_GA);
    }

    /**
     * GetStoreCurrencyCode
     */
    public function getStoreCurrencyCode()
    {
        return $this->store->getStore()->getCurrentCurrencyCode();
    }

    /**
     * GetBaseCurrencyCode
     */
    public function getBaseCurrencyCode()
    {
        return $this->store->getStore()->getBaseCurrencyCode();
    }

    /**
     * GetDataStorageExpiryTime
     */
    public function getDataStorageExpiryTime()
    {
        return $this->getConfig(self::XML_PATH_GA4_DATALAYER);
    }

    /**
     * GetProductTypeForCheckout
     */
    public function getProductTypeForCheckout()
    {
         return $this->getConfig(self::XML_PATH_GA4_CONFIGURABLEITEMS);
    }

    /**
     * GetIsAllowOrderSummary
     */
    public function getIsAllowOrderSummary()
    {
        return $this->getConfig(self::XML_PATH_GA4_ALLOWORDERSUMMARY);
    }

    /**
     * GetSummaryExcludeTax
     */
    public function getSummaryExcludeTax()
    {
        return $this->getConfig(self::XML_PATH_GA4_SUMMARY_TAX_EXCLUDE);
    }

    /**
     * GetSummaryExcludeShipping
     */
    public function getSummaryExcludeShipping()
    {
        return $this->getConfig(self::XML_PATH_GA4_SUMMARY_SHIPPING_EXCLUDE);
    }

    /**
     * GetCheckoutSuccessLandingPage
     */
    public function getCheckoutSuccessLandingPage()
    {
        return $this->getConfig(self::XML_PATH_GA4_CHECKOUT_PATH_HINT);
    }

    /**
     * GetPublicId
     */
    public function getPublicId()
    {
        return $this->getConfig(self::XML_PATH_GA4_JSON_EXPORT_PUBLIC_ID);
    }

    /**
     * GetCurrentProduct
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * GetCurrentCategory
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * GetCurrentUrl
     */
    public function getCurrentUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

    /**
     * GetCurrencyOption
     */
    public function getCurrencyOption()
    {
        return $this->getConfig(self::XML_PATH_GA4_CURRENCY);
    }

    /**
     * NotLoggedInCustomerIdEnabled
     */
    public function NotLoggedInCustomerIdEnabled() // phpcs:ignore
    {
        return $this->getConfig(self::XML_PATH_GA4_ALLOW_NOT_LOGGEDIN_CUSTOMER);
    }

    /**
     * GetCode
     */
    public function getCode()
    {
        $code = '';
        $code = $this->checkoutsession->getQuote()->getCouponCode();
        return $code;
    }

    /**
     * GetPriceFormat
     *
     * @param Product $product
     */
    public function getPriceFormat($product)
    {
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
        return $finalPrice;
    }

    /**
     * GetCatIds
     *
     * @param CatIds $catIds
     */
    public function getCatIds($catIds)
    {
        if (!count((array)$catIds)) {
            return '';
        }
        if (empty($this->getStoreLevelCategories)) {
            $this->storeCat();
        }
        //$catId = $catIds[0];
        $catPath = '';
        $catName = [];
        $countCatId = count((array)$catIds);
        if (!empty($catIds)) {
            // @codingStandardsIgnoreStart
            for ($i = 0; $i < $countCatId; $i++) {
                if (isset($this->getStoreLevelCategories[$catIds[$i]])) {
                    $catPath = $this->getStoreLevelCategories[$catIds[$i]]['path'];
                    $name = $this->catPath($catPath);
                    $catName = array_merge($catName, $name);
                }
                $catName = array_values(array_unique($catName));
                if (count($catName) >= 5) {
                    break;
                }
            }
            // @codingStandardsIgnoreEnd
        }
        return array_slice($catName, 0, 5, true);
    }

    /**
     * CatPath
     *
     * @param CatPath $catPath
     */
    public function catPath($catPath)
    {
        $categIds = explode('/', $catPath);
        $catId = end($categIds);
        $catName = [];
        if (isset($this->getStoreLevelCategories[$catId])) {
            $catName[] = $this->getStoreLevelCategories[$catId]['name'];
        }

        return $catName;
    }

    /**
     * StoreCat
     */
    public function storeCat()
    {
        if (!$this->isGTMStatus() || !empty($this->getStoreLevelCategories)) {
            return;
        }

        $rootCategoryId = $this->store->getStore()->getRootCategoryId();
        $storeId = $this->store->getStore()->getStoreId();

        $mdCacheStatus = $this->stateInterface->isEnabled(Option::MD_CAT);
        $cacheKey = 'magedelight_ga4_cached_categories-' . $rootCategoryId . '-' . $storeId;
        if ($mdCacheStatus) {
            $this->_eventManager->dispatch(
                'magedelight_ga4_cachekey_after',
                ['cache_key' => $cacheKey]
            );
            $cachedCategoriesData = $this->cacheInterface->load($cacheKey);
            if ($cachedCategoriesData) {
                $this->getStoreLevelCategories = json_decode($cachedCategoriesData, true);
                return;
            }
        }
        $categories = $this->catalogCollection->create()
            ->setStoreId($storeId)
            ->addAttributeToFilter('path', ['like' => "1/{$rootCategoryId}%"])
            ->addAttributeToSelect('name');
        foreach ($categories as $categ) {
            $this->getStoreLevelCategories[$categ->getData('entity_id')] = [
                'name' => $categ->getData('name'),
                'path' => $categ->getData('path')
            ];
        }
        if ($mdCacheStatus) {
            $cachedCategories = json_encode($this->getStoreLevelCategories);
            $this->cacheInterface->save($cachedCategories, $cacheKey, [Option::MD_TAG]);
        }
    }

    /**
     * GetNameOfAffilation
     */
    public function getNameOfAffilation()
    {
        $name=$this->store->getWebsite()->getName().
        ' - '.$this->store->getGroup()->getName().
        ' - '.$this->store->getStore()->getName();
        return $name;
    }

    /**
     * GetCurrentCat
     *
     * @param Category $category
     */
    public function getCurrentCat($category)
    {
        $categoryPath = $category->getData('path');
        $this->storeCat();

        return $this->catPath($categoryPath);
    }

    /**
     * GetDateTimeZone
     *
     * @param Date $date
     */
    public function getDateTimeZone($date)
    {
        return $this->timezoneInterface->date(new \DateTime($date))->format("M j, Y h:i:s A");
    }

    /**
     * SetTagManagerReportData
     *
     * @param array $dataevent
     */
    public function setTagManagerReportData($dataevent)
    {
        if ($this->getConfig(self::XML_PATH_GA4_REALTIME_TRACKING)) {
            if ($this->GTMEventConfigured($dataevent['event'])) {
                $eventLabel = $this->getCurrentUrl();
                if ($dataevent['event'] == "add_to_cart") {
                    $eventLabel = substr($eventLabel, 0, -1);
                    $url = substr($eventLabel, 0, strpos($eventLabel, "uenc"));
                    $keys = parse_url($eventLabel); // phpcs:ignore
                    $path = explode("/", $keys['path']);
                    $end = end($path);
                    $prev = prev($path);
                    $eventLabel = $url.$prev.'/'.$end.'/';
                }
                if (($dataevent['event']=="view_item_list" ||
                    $dataevent['event']== "view_item") &&
                    (strpos($eventLabel, '?') !== false)) {
                    $eventLabel = substr($eventLabel, 0, strpos($eventLabel, "?"));
                }
                $model = $this->gtagFactory->create();
                $model->setEventName($dataevent['event']);
                $model->setEventLabel($eventLabel);
                $model->setUserEmail($this->getCustomerData());
                $model->setEventData(json_encode($dataevent));
                $db_save = $this->getConfig(self::XML_DATABASE_SAVE);
                if ($db_save == 1) {
                    $model->save();
                }
            }
        }
    }

    /**
     * BrandEnabled
     */
    public function isBrandEnabled()
    {
        return $this->getConfig(self::XML_PATH_GA4_BRAND_STATUS);
    }

    /**
     * GetBrandData
     *
     * @param Product $product
     */
    public function getBrandData($product)
    {
        $gtmBrand = null;
        if ($this->isBrandEnabled()) {
            $brandAttribute = $this->getConfig(self::XML_PATH_GA4_BRAND_ATTRIBUTE_CODE);
            if ($brandAttribute) {
                if ($product->getResource()->getAttribute($brandAttribute)) {
                    $gtmBrand = $product->getResource()->getAttribute($brandAttribute)
                        ->getFrontend()->getValue($product);
                }
            }
        }
        return ($gtmBrand) ? $gtmBrand : null;
    }

    /**
     * Get Catalog Price Rule From Product.
     *
     * @param mixed $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCatalogPriceRuleFromProduct($product)
    {
        $websiteId = $this->store->getStore()->getWebsiteId();
        $date = $this->timezoneInterface->date(new \DateTime())->format("Y-m-j h:i:s");

        if ($this->customerSession->isLoggedIn()) {
            $customerGroupId =  $this->customerSession->getCustomer()->getGroupId();
        } else {
            $customerGroupId =  0;
        }

        $rules = $this->catalogRule->getRulesFromProduct(
            $date,
            $websiteId,
            $customerGroupId,
            $product->getData('entity_id')
        );
        foreach ($rules as $rule) {
            $rule = $this->catalogRuleRepository->get($rule['rule_id']);
            $rules = [];
            $rules['id'] = $rule->getRuleId();
            $rules['name'] = $rule->getName();
        }
        return $rules;
    }

    /**
     * GetProductIdentifier
     *
     * @param Product $product
     */
    public function getProductIdentifier($product)
    {
        $identifier = $this->getConfig(self::XML_PATH_GA4_IDENTIFIER);
        $getId = '';
        switch ($identifier) {
            case 'id':
                $getId = $product->getId();
                break;
            case 'sku':
            default:
                $getId = $product->getData('sku');
                break;
        }

        return $getId;
    }

    /**
     * GetProductOption
     *
     * @param Option $option
     * @param Type $type
     */
    public function getProductOption($option, $type)
    {
        $optionType = [];
        if ($type == 'configurable') {
            if (isset($option['attributes_info'])) {
                foreach ($option['attributes_info'] as $optionAttribute) {
                    $optionType[] = $optionAttribute['label'] . ": " . $optionAttribute['value'];
                }
            }
        }

        if (isset($option['options'])) {
            foreach ($option['options'] as $optVal) {
                $optionType[] = $optVal['label'] . ": " . $optVal['print_value'];
            }
        }

        if ($optionType) {
            return implode(' | ', $optionType);
        }

        return false;
    }

    /**
     * GetCurrencyCode
     */
    public function getCurrencyCode()
    {
        $currencyOption = $this->getCurrencyOption();
        if ($currencyOption == "base_currency") {
            $code = $this->getBaseCurrencyCode();
        } else {
            $code = $this->getStoreCurrencyCode();
        }
        return $code;
    }

    /**
     * GetCustomerData
     */
    public function getCustomerData()
    {
        $user = null;
        if ($this->getIsCustomerLoggedIn()) {
            $user = $this->_httpContext->getValue('customer_email');
        } else {
            if ($this->customerSession->isLoggedIn()) {
                if ($this->getConfig(self::XML_PATH_GA4_SHOW_EMAIL)) {
                    $user = $this->customerSession->getCustomer()->getEmail();
                }
            } else {
                $user = "Visitor";
            }
        }
        return $user;
    }

    /**
     * GetIsCustomerLoggedIn
     */
    public function getIsCustomerLoggedIn()
    {
        return (bool)$this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * GetCookie
     */
    public function getCookie()
    {
        return $this->cookieManager->getCookie(
            'user_allowed_save_cookie'
        );
    }

    /**
     * GetGTMEvents
     */
    public function getGTMEvents()
    {
        $eventList =  $this->getConfig(self::XML_PATH_GA4_GTM_EVENT);
        return $eventList !== null ? explode(',', $eventList) : [];
    }

    /**
     * GTMEventConfigured
     *
     * @param mixed $event
     * @return bool
     */
    public function GTMEventConfigured($event)
    {
        $events = $this->getGTMEvents();
        return in_array($event, $events);
    }

    /**
     * Get Custom Attribute.
     *
     * @param mixed $path
     * @return mixed
     */
    public function getCustomAttribute($path)
    {
        $path = self::XML_PATH_GA4_PRODUCT_CUSTOM_OPTION . $path;
        return $this->getConfig($path);
    }

    /**
     * Get Product Custom Attribute.
     *
     * @param mixed $item
     * @return array
     */
    public function getProductCustomAttribute($item)
    {
        $customProductAttribute = [];
        $value = "";
        for ($i=1; $i<=5; $i++) {
            if ($this->getCustomAttribute($i)) {
                $code = $this->getCustomAttribute($i."_code");
                try {
                    $frontendValue = $item->getAttributeText($code);
                    if (is_array($frontendValue)) {
                        $value = implode(',', $item->getAttributeText($code));
                    } else {
                        $value = trim($item->getAttributeText($code));
                    }
                    if (!$value) {
                        $value = $item->getData($code);
                    }
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                }
                $customProductAttribute['item_'.$code] = ($value) ? strip_tags($value ?? "") : null;
            }
        }
        return $customProductAttribute;
    }

    /**
     * Function getFormatedPrice
     *
     * @param float $price
     *
     * @return string
     */
    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->convert($price);
    }

    /**
     * GetGDPRStatus
     */
    public function getPromotionTracking()
    {
        return $this->getConfig(self::XML_PATH_GA4_PROMOTION_TRACKING);
    }

    /**
     * Check variant enabled.
     *
     * @return mixed
     */
    public function isVariantEnabled()
    {
        return $this->getConfig(self::XML_PATH_GA4_PRODUCT_VARIANT);
    }

    /**
     * Get Item Price.
     *
     * @param mixed $item
     * @return string
     */
    public function getItemPrice($item)
    {
        $currencyOption = $this->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return (float)$item->getPrice();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $this->getFormatedPrice($item->getPrice());
        }
    }

    /**
     * Retrieve checkout items details from the provided quote.
     *
     * @param \Magento\Quote\Model\Quote $quote The quote object containing all visible items.
     * @return array
     */
    public function getCheckoutItems($quote)
    {
        $products = [];
        $type = $this->getProductTypeForCheckout();
        $k = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($type == ProductType::CHILD) {
                if ($item->getProductType()== Configurable::TYPE_CODE) {
                    $children = $item->getChildren();
                    foreach ($children as $child) {
                        $product = $child->getProduct();
                    }
                }
            }

            $productDetail = [];
            $productDetail['item_id'] = $this->getProductIdentifier($product);
            $productDetail['item_name'] = $product->getName();
            $productDetail['affiliation'] = $this->getNameOfAffilation();
            $productDetail['coupon'] = $quote->getCouponCode();
            $productDetail['index'] = $k;
            $categoryName =  $this->getCatIds($product->getCategoryIds());
            if (!empty($categoryName)) {
                $j = 1;
                $countCategoryName = count($categoryName);
                for ($i = 0; $i < $countCategoryName; $i++) {
                    if ($j > 1) {
                        $productDetail['item_category'.$i] = $categoryName[$i];
                    } else {
                        $productDetail['item_category'] = $categoryName[$i];
                    }
                    $j++;
                }
                $productDetail['item_list_name'] = implode('/', $categoryName);
                $productCategoryIds = $product->getCategoryIds();
                $productDetail['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : null;
            }

            if ($this->isVariantEnabled()) {
                $option = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $ptype = $item->getProductType();
                $variant = $this->getProductOption($option, $ptype);
                if ($variant) {
                    $productDetail['item_variant'] = $variant;
                }
            }

            $productDetail['price'] = $this->getItemPrice($item);
            $productDetail['quantity'] = $item->getQty();

            $originProduct = $this->getLoadProduct($product->getEntityId());
            if ($this->isBrandEnabled()) {
                $productDetail['item_brand'] = $this->getBrandData($originProduct);
            }
            $customAttribute = $this->getProductCustomAttribute($originProduct);
            if (!empty($customAttribute)) {
                foreach ($customAttribute as $key => $value) {
                    $productDetail[$key] = $value;
                }
            }

            $products[] = $productDetail;
            $k++;
        }

        return $products;
    }

    /**
     * GetLoadProduct
     *
     * @param ID $id
     */
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    /**
     * Retrieve order items along with their detailed attributes and information.
     *
     * @param mixed $order The order object for which items need to be retrieved.
     * @return array List of order items and their corresponding details and attributes.
     */
    public function getOrderItems($order)
    {
        $products = [];
        $type = $this->getProductTypeForCheckout();
        $k =0;
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($type == ProductType::CHILD) {
                if ($item->getProductType()== Configurable::TYPE_CODE) {
                    $children = $item->getChildrenItems();
                    if ($children) {
                        foreach ($children as $child) {
                            $product = $child->getProduct();
                        }
                    }
                }
            }

            $productDetail = [];
            $productDetail['item_id'] = $this->getProductIdentifier($product);
            $productDetail['item_name'] = $product->getName();
            $productDetail['affiliation'] = $this->getNameOfAffilation();
            $productDetail['coupon'] = $order->getCouponCode();
            $productDetail['index'] = $k;
            $categoryName =  $this->getCatIds($product->getCategoryIds());
            if (!empty($categoryName)) {
                $j = 1;
                $categoryNameCount = count($categoryName);
                for ($i = 0; $i < $categoryNameCount; $i++) {
                    if ($j>1) {
                        $productDetail['item_category'.$i] = $categoryName[$i];
                    } else {
                        $productDetail['item_category'] = $categoryName[$i];
                    }
                    $j++;
                }
                $productDetail['item_list_name'] = implode('/', $categoryName);
                $productCategoryIds = $product->getCategoryIds();
                $productDetail['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : null;
            }

            if ($this->isVariantEnabled()) {
                $option = $item->getProductOptions();
                $ptype = $item->getProductType();
                $variant = $this->getProductOption($option, $ptype);
                if ($variant) {
                    $productDetail['item_variant'] = $variant;
                }
            }

            $productDetail['price'] = $this->getItemPrice($item);
            $productDetail['quantity'] = (float)$item->getQtyOrdered();

            $originProduct = $this->getLoadProduct($product->getEntityId());
            if ($this->isBrandEnabled()) {
                $productDetail['item_brand'] = $this->getBrandData($originProduct);
            }
            $customAttribute = $this->getProductCustomAttribute($originProduct);
            if (!empty($customAttribute)) {
                foreach ($customAttribute as $key => $value) {
                    $productDetail[$key] = $value;
                }
            }

            $products[] = $productDetail;
            $k++;
        }
        return $products;
    }
}
