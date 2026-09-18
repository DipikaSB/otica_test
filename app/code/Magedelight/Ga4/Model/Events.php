<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model;

use Magedelight\Ga4\lib\Google\Client as Google_Client;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Events extends \Magento\Framework\Model\AbstractModel
{
    public const XML_PATH_GA4_API_KEY = 'magedelight_ga4/general/ga4_api_key';

    /***Variables***/
    public const MD_VAR_MEASUREMENT_ID = 'MEASUREMENT ID';
    public const MD_VAR_CUSTOMER_ID = 'MD - CustomerId';
    public const MD_VAR_CUSTOMER_GROUP = 'MD - customerGroup';
    public const MD_VAR_PAGE_TYPE = 'MD - Page Type';
   // public const MD_VAR_ITEMS = 'MD - ecommerce.items';
    public const MD_VAR_PURCHASE_ITEMS = 'MD - Ecommerce Items';
    public const MD_VAR_SHIPPING_TIER = 'MD - Ecommerce Shipping Tier';
   // public const MD_VAR_PURCHASE_ITEMS = 'MD - ecommerce.refund.items';
    //public const MD_VAR_PURCHASE_ITEMS = 'MD - ecommerce.action.items';
    public const MD_VAR_TRANSACTION_ID = 'MD - Ecommerce Transaction ID';
    public const MD_VAR_TAX = 'MD - Ecommerce Tax';
    public const MD_VAR_COUPON = 'MD - Ecommerce Coupon';
    public const MD_VAR_SHIPPING = 'MD - Ecommerce Shipping';
    public const MD_VAR_CURRENCY = 'MD - Ecommerce Currency';
    public const MD_VAR_PAYMENT_TYPE = 'MD - Ecommerce Payment Type';
    public const MD_VAR_AFFILIATION = 'MD - Affiliation';
    public const MD_VAR_ORDER_VALUE = 'MD - Order Value';
    public const MD_VAR_CUSTOMER_ORDER_COUNT = 'MD - Customer - order_count';
    public const MD_VAR_CUSTOMER_ALL_TOTAL_VALUE = 'MD - Customer - all_order_value';
    public const MD_VAR_PURCHASE_VALUE = 'MD - Ecommerce Value';
    public const MD_VAR_REFUND_VALUE = 'MD - Refund Value';
    public const MD_VAR_ITEM_LIST_ID = 'MD - Ecommerce Item List ID';
    public const MD_VAR_ITEM_LIST_NAME = 'MD - Ecommerce Item List Name';
    public const MD_VAR_CREATIVE_NAME = 'MD - Ecommerce Creative Name';
    public const MD_VAR_CREATIVE_SLOT = 'MD - Ecommerce Creative Slot';
    public const MD_VAR_PROMOTION_ID = 'MD - Ecommerce Promotion ID';
    public const MD_VAR_PROMOTION_NAME = 'MD - Ecommerce Promotion Name';

    /***Triggers***/
    public const MD_GTM_DOM = 'MD - gtm.dom';
    public const MD_ADD_TO_CART = 'MD - add_to_cart';
    public const MD_REMOVE_FROM_CART = 'MD - remove_from_cart';
    public const MD_VIEW_ITEM = 'MD - view_item';
    public const MD_VIEW_ITEM_LIST = 'MD - view_item_list';
    public const MD_BEGIN_CHECKOUT = 'MD - begin_checkout';
    public const MD_PURCHASE = 'MD - purchase';
    public const MD_REFUND = 'MD - refund';
    public const MD_ADD_TO_WISHLIST = 'MD - Add To Wishlist';
    public const MD_ADD_TO_COMPARE = 'MD - Add To Compare';
    public const MD_SELECT_ITEM = 'MD - select_item';
    public const MD_VIEW_CART = 'MD - View Cart';
    public const MD_ADD_PAYMENT_INFO = 'MD - Add Payment Info';
    public const MD_ADD_SHIPPING_INFO = 'MD - Add Shipping Info';
    public const MD_VIEW_PROMOTION = 'MD - view_promotion';
    public const MD_SELECT_PROMOTION = 'MD - select_promotion';

    /**
     * Tags
     */
    public const MD_TAG_MEASUREMENT_ID = 'MD - GA4';
    public const MD_ITEM_LIST_VIEWS_IMPRESSIONS = 'MD - item list views/impressions';
    public const MD_TAG_ITEM_LIST_VIEWS = 'MD - Views Item List';
    public const MD_TAG_PRODUCT_ITEM_LIST_CLICKS = 'MD - product/item list clicks';
    public const MD_TAG_ITEM_ADD_TO_CART = 'MD - Add to Cart';
    public const MD_TAG_ITEM_REMOVE_FROM_CART = 'MD - Remove from Cart';
    public const MD_ITEM_VIEWS_IMPRESSIONS = 'MD - item views/impressions';
    public const MD_TAG_BEGIN_CHECKOUT = 'MD - Begin Checkout';
    public const MD_TAG_PURCHASE = 'MD - Purchase';
    public const MD_TAG_REFUND = 'MD - Refund';

    public const MD_TAG_ADD_TO_WISHLIST = 'MD - Add to Wishlist';
    public const MD_TAG_ADD_TO_COMPARE = 'MD - Add to Compare';
    public const MD_TAG_VIEW_CART = 'MD - View Cart';
    public const MD_TAG_ADD_PAYMENT_INFO = 'MD - Add Payment Info';
    public const MD_TAG_ADD_SHIPPING_INFO = 'MD - Add Shipping Info';

    public const MD_TYPE_VARIABLE_DATALAYER = 'v';
    public const MD_TYPE_VARIABLE_CONSTANT = 'c';
    public const MD_TYPE_TRIGGER_CUSTOM_EVENT = 'customEvent';
    public const MD_TYPE_TRIGGER_PAGEVIEW = 'pageview';
    public const MD_TAG_VIEW_PROMOTION = 'MD - View Promotion';
    public const MD_TAG_SELECT_PROMOTION = 'MD - Select Promotion';

    /**
     * @var $client
     */
    private $client = null;

    /**
     * @var $scopes
     */
    private $scopes =
    [
        'https://www.googleapis.com/auth/tagmanager.edit.containers',
        'https://www.googleapis.com/auth/tagmanager.readonly',
        'https://www.googleapis.com/auth/userinfo.profile',

    ];

    /**
     * @var $apiLinkUrl
     */
    protected $apiLinkUrl = 'https://www.googleapis.com/tagmanager/v1/';

    /**
     * @var $urlBuilder
     */
    protected $urlBuilder;

    /**
     * @var $backendSession
     */
    protected $backendSession;

    /**
     * @var $equest
     */
    protected $request;

    /**
     * @var $storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param Session $backendSession
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $urlBuilder,
        Session $backendSession,
        Http $request,
        StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        parent::__construct($context, $registry);
        $this->urlBuilder = $urlBuilder;
        $this->backendSession = $backendSession;
        $this->request = $request;
        $this->_storeManager=$storeManager;
        $this->_curl = $curl;
    }

    /**
     * GetGTMClient
     */
    public function getGTMClient()
    {
        if (!$this->client) {
            $clientId = $this->scopeConfig->getValue(self::XML_PATH_GA4_API_KEY, ScopeInterface::SCOPE_STORE);
            $this->client = new Google_Client();
            $storeName = $this->_storeManager->getStore()->getName();
            if ($storeName):
                $this->client->setApplicationName($storeName.'-Ga4');
            else:
                $this->client->setApplicationName('Magedelight-Ga4');
            endif;
            $this->client->setAPIKey($clientId);
            $this->client->setScopes($this->scopes);
            $storeId = $this->request->getParam('store');
            $websiteId = $this->request->getParam('website');
            $returnUrl = $this->urlBuilder->getUrl(
                "adminhtml/system_config/edit",
                [
                    'section' => 'magedelight_ga4',
                    'website' => $websiteId,
                    'store' => $storeId
                ]
            );
            $this->client->setState($returnUrl);
            $this->client->setRedirectUri($this->_storeManager->getStore()->getBaseUrl());

            $request = $this->request->getParam('code');
            if ($request) {
                try {
                    $this->getGTMClient()->authenticate($request);
                    $this->_backendSession->setAccessToken($this->client->getAccessToken());
                } catch (\Exception $ex) {
                    return $ex->getMessage();
                };
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                header('Location: ' . $returnUrl);
                return;
            }

            $token = $this->_backendSession->getAccessToken();
            if ($token) {
                $this->client->setAccessToken($token);
            }
        }

        return $this->client;
    }

    /**
     * GetApiLinkUrl
     */
    protected function getApiLinkUrl()
    {
        return $this->apiLinkUrl;
    }

    /**
     * DrawEventItems
     *
     * @param Option $option
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param TrackingId $trackingId
     * @param Ipaddress $ipaddress
     * @param ShowDisplayAds $showDisplayAds
     */
    public function drawEventItems($option, $accountId, $containerId, $trackingId, $ipaddress, $showDisplayAds)
    {
        $data = [];
        switch ($option) {
            case 'variables':
                $data = $this->getGenerateVar($accountId, $containerId, $trackingId);
                break;
            case 'triggers':
                $data = $this->getGenerateTrgg($accountId, $containerId);
                break;
            case 'tags':
                $data = $this->getGenerateTags($accountId, $containerId, $ipaddress, $showDisplayAds);
                break;
        }

        return $data;
    }

    /**
     * GetGenerateVar
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param TrackingId $trackingId
     */
    protected function getGenerateVar($accountId, $containerId, $trackingId)
    {
        $existingData = $this->getSavedVars($accountId, $containerId);

        $result = [];
        $data = [];

        foreach ($existingData as $variable) {
            $data[$variable['name']] = true;
        }

        $getToCreateVar = $this->getValueOfVar($trackingId);

        foreach ($getToCreateVar as $name => $options) {
            if (isset($data[$name])) {
                continue;
            }
            try {
                $result = $this->createAccountVar($accountId, $containerId, $options);
                if ($result['variableId']) {
                    $result[] = __('Successfully created variable: ') . $result['name'];
                } else {
                    $result[] = __('Error while creating variable: ') . $result['name'];
                }
            } catch (\Exception $ex) {
                $result[] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * GetGenerateTrgg
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     */
    protected function getGenerateTrgg($accountId, $containerId)
    {
        $existingTriggersData = $this->getAlreadyCreatedTriggers($accountId, $containerId);

        $result = [];
        $triggerDataFlags = [];

        foreach ($existingTriggersData as $trigger) {
            $triggerDataFlags[$trigger['name']] = true;
        }

        $triggersToCreate = $this->getTriggers();

        foreach ($triggersToCreate as $name => $options) {
            if (isset($triggerDataFlags[$name])) {
                continue;
            }
            try {
                $getResponse = $this->getCreateTrigger($accountId, $containerId, $options);
                if ($getResponse['triggerId']) {
                    $result[] = __('Successfully created trigger: ') . $getResponse['name'];
                } else {
                    $result[] = __('Error creating trigger: ') . $getResponse['name'];
                }
            } catch (\Exception $ex) {
                $result[] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * GetGenerateTags
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param Ipaddress $ipaddress
     * @param ShowDisplayAds $showDisplayAds
     */
    protected function getGenerateTags($accountId, $containerId, $ipaddress, $showDisplayAds)
    {
        $ipaddress = ($ipaddress) ? 'true' : 'false';
        $showDisplayAds = ($showDisplayAds) ? 'true' : 'false';
        $createdTags = $this->getCraetedTag($accountId, $containerId);

        $result = [];
        $tagData = [];

        foreach ($createdTags as $tag) {
            $tagData[$tag['name']] = true;
        }

        $triggersMapping = $this->getMappingOfTriggers($accountId, $containerId);
        $tagDataGen = $this->getTagData($triggersMapping);

        foreach ($tagDataGen as $name => $options) {
            if (isset($tagData[$name])) {
                continue;
            }
            try {
                $tagsResponse = $this->getTagsCreated($accountId, $containerId, $options);
                if ($tagsResponse['tagId']) {
                    $result[] = __('Successfully created tag: ') . $tagsResponse['name'];
                } else {
                    $result[] = __('Error creating tag: ') . $tagsResponse['name'];
                }
            } catch (\Exception $ex) {
                $result[] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * GetMappingOfTriggers
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     */
    protected function getMappingOfTriggers($accountId, $containerId)
    {
        $triggers = $this->getAlreadyCreatedTriggers($accountId, $containerId);
        $triggersMap = [];

        foreach ($triggers as $trigger) {
            $triggersMap[$trigger['name']] = $trigger['triggerId'];
        }

        return $triggersMap;
    }

    /**
     * LimitationOfApi
     */
    protected function limitationOfApi()
    {
        usleep(4000000);
    }

    /**
     * GetVariablesList
     *
     * @param TrackingId $trackingId
     */
    public function getVariablesList($trackingId)
    {
        return $this->getValueOfVar($trackingId);
    }

    /**
     * GetTriggersList
     */
    public function getTriggersList()
    {
        return $this->getTriggers();
    }

    /**
     * GetTagsList
     *
     * @param TriggersMapping $triggersMapping
     */
    public function getTagsList($triggersMapping)
    {
        return $this->getTagData($triggersMapping);
    }

    /**
     * GetValueOfVar
     *
     * @param TrackingId $trackingId
     */
    private function getValueOfVar($trackingId)
    {
        $eventsValues = [
            self::MD_VAR_MEASUREMENT_ID => [
                'name' => self::MD_VAR_MEASUREMENT_ID,
                'type' => self::MD_TYPE_VARIABLE_CONSTANT,
                'parameter' => [
                    [
                        'type' => 'template',
                        'key' => 'value',
                        'value' => $trackingId
                    ]
                ]
            ],
            self::MD_VAR_CUSTOMER_GROUP => [
                'name' => self::MD_VAR_CUSTOMER_GROUP,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'customerGroup'
                    ]
                ]
            ],
            self::MD_VAR_CUSTOMER_ID => [
                'name' => self::MD_VAR_CUSTOMER_ID,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'customerId'
                    ]
                ]
            ],
            self::MD_VAR_PAGE_TYPE => [
                'name' => self::MD_VAR_PAGE_TYPE,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'pageType'
                    ]
                ]
            ],
            self::MD_VAR_PURCHASE_ITEMS => [
                'name' => self::MD_VAR_PURCHASE_ITEMS,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.items'
                    ]
                ]
            ],
            /*self::MD_VAR_PURCHASE_ITEMS => [
                'name' => self::MD_VAR_PURCHASE_ITEMS,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.refund.items'
                    ]
                ]
            ],*/
            /*self::MD_VAR_PURCHASE_ITEMS => [
                'name' => self::MD_VAR_PURCHASE_ITEMS,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.action.items'
                    ]
                ]
            ],*/
            self::MD_VAR_PURCHASE_VALUE => [
                'name' => self::MD_VAR_PURCHASE_VALUE,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.value'
                    ]
                ]
            ],
            self::MD_VAR_REFUND_VALUE => [
                'name' => self::MD_VAR_REFUND_VALUE,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.refund.value'
                    ]
                ]
            ],
            /*self::MD_VAR_ITEMS => [
                'name' => self::MD_VAR_ITEMS,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.items'
                    ]
                ]
            ],*/
            self::MD_VAR_COUPON => [
                'name' => self::MD_VAR_COUPON,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.coupon'
                    ]
                ]
            ],
            self::MD_VAR_ORDER_VALUE => [
                'name' => self::MD_VAR_ORDER_VALUE,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'value'
                    ]
                ]
            ],
            self::MD_VAR_CUSTOMER_ORDER_COUNT => [
                'name' => self::MD_VAR_CUSTOMER_ORDER_COUNT,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.purchase.order_count'
                    ]
                ]
            ],
            self::MD_VAR_TRANSACTION_ID => [
                'name' => self::MD_VAR_TRANSACTION_ID,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.transaction_id'
                    ]
                ]
            ],
            self::MD_VAR_SHIPPING => [
                'name' => self::MD_VAR_SHIPPING,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.shipping'
                    ]
                ]
            ],
            self::MD_VAR_CURRENCY => [
                'name' => self::MD_VAR_CURRENCY,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.currency'
                    ]
                ]
            ],
            self::MD_VAR_AFFILIATION => [
                'name' => self::MD_VAR_AFFILIATION,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.purchase.affiliation'
                    ]
                ]
            ],
            self::MD_VAR_TAX => [
                'name' => self::MD_VAR_TAX,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.tax'
                    ]
                ]
            ],
            self::MD_VAR_CUSTOMER_ALL_TOTAL_VALUE => [
                'name' => self::MD_VAR_CUSTOMER_ALL_TOTAL_VALUE,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.purchase.all_order_value'
                    ]
                ]
            ],
            self::MD_VAR_PAYMENT_TYPE=> [
                'name' => self::MD_VAR_PAYMENT_TYPE,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.payment_type'
                    ]
                ]
            ],
            self::MD_VAR_SHIPPING_TIER=> [
                'name' => self::MD_VAR_SHIPPING_TIER,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.shipping_tier'
                    ]
                ]
            ],
            self::MD_VAR_ITEM_LIST_ID=> [
                'name' => self::MD_VAR_ITEM_LIST_ID,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.item_list_id'
                    ]
                ]
            ],
            self::MD_VAR_ITEM_LIST_NAME=> [
                'name' => self::MD_VAR_ITEM_LIST_NAME,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.item_list_name'
                    ]
                ]
            ],
            self::MD_VAR_CREATIVE_NAME=> [
                'name' => self::MD_VAR_CREATIVE_NAME,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.creative_name'
                    ]
                ]
            ],
            self::MD_VAR_CREATIVE_SLOT=> [
                'name' => self::MD_VAR_CREATIVE_SLOT,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.creative_slot'
                    ]
                ]
            ],
            self::MD_VAR_PROMOTION_NAME=> [
                'name' => self::MD_VAR_PROMOTION_NAME,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.promotion_name'
                    ]
                ]
            ],
            self::MD_VAR_PROMOTION_ID=> [
                'name' => self::MD_VAR_PROMOTION_ID,
                'type' => self::MD_TYPE_VARIABLE_DATALAYER,
                'parameter' => [
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ],
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'ecommerce.promotion_id'
                    ]
                ]
            ]

        ];
        return $eventsValues;
    }

    /**
     * GetTriggers
     */
    private function getTriggers()
    {
        $triggers = [
            self::MD_GTM_DOM => [
                'name' => self::MD_GTM_DOM,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'gtm.dom'
                            ]
                        ]
                    ]
                ],
                'filter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{Event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'gtm.dom'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_SELECT_ITEM => [
                'name' => self::MD_SELECT_ITEM,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'select_item'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_ADD_TO_CART => [
                'name' => self::MD_ADD_TO_CART,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_to_cart'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_REMOVE_FROM_CART => [
                'name' => self::MD_REMOVE_FROM_CART,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'remove_from_cart'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_VIEW_CART => [
                'name' => self::MD_VIEW_CART,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_cart'
                            ]
                        ]
                    ]
                ]
            ],

            self::MD_ADD_TO_WISHLIST => [
                'name' => self::MD_ADD_TO_WISHLIST,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_to_wishlist'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_ADD_TO_COMPARE => [
                'name' => self::MD_ADD_TO_COMPARE,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_to_compare'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_ADD_PAYMENT_INFO => [
                'name' => self::MD_ADD_PAYMENT_INFO,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_payment_info'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_ADD_SHIPPING_INFO => [
                'name' => self::MD_ADD_SHIPPING_INFO,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_shipping_info'
                            ]
                        ]
                    ]
                ]
            ],

            self::MD_BEGIN_CHECKOUT => [
                'name' => self::MD_BEGIN_CHECKOUT,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'begin_checkout'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_VIEW_ITEM => [
                'name' => self::MD_VIEW_ITEM,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_item'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_VIEW_ITEM_LIST => [
                'name' => self::MD_VIEW_ITEM_LIST,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_item_list'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_PURCHASE => [
                'name' => self::MD_PURCHASE,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'purchase'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_REFUND => [
                'name' => self::MD_REFUND,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'refund'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_VIEW_PROMOTION => [
                'name' => self::MD_VIEW_PROMOTION,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_promotion'
                            ]
                        ]
                    ]
                ]
            ],
            self::MD_SELECT_PROMOTION => [
                'name' => self::MD_SELECT_PROMOTION,
                'type' => self::MD_TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'select_promotion'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        return $triggers;
    }

    /**
     * GetTagData
     *
     * @param Triggers $triggers
     */
    private function getTagData($triggers)
    {
        $tags = [
            self::MD_TAG_MEASUREMENT_ID => [
                'name' => self::MD_TAG_MEASUREMENT_ID,
                'firingTriggerId' => [
                    '2147479553'
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawc',
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'sendPageView',
                        'value' => "true"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::MD_VAR_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_ITEM_LIST_VIEWS_IMPRESSIONS => [
                'name' => self::MD_ITEM_LIST_VIEWS_IMPRESSIONS,
                'firingTriggerId' => [
                    $triggers[self::MD_VIEW_ITEM_LIST]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'view_item_list'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_ITEM_LIST_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_ITEM_LIST_NAME . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_PRODUCT_ITEM_LIST_CLICKS => [
                'name' => self::MD_TAG_PRODUCT_ITEM_LIST_CLICKS,
                'firingTriggerId' => [
                    $triggers[self::MD_SELECT_ITEM]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'select_item'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_ITEM_ADD_TO_CART => [
                'name' => self::MD_TAG_ITEM_ADD_TO_CART,
                'firingTriggerId' => [
                    $triggers[self::MD_ADD_TO_CART]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'add_to_cart'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_ADD_TO_WISHLIST => [
                'name' => self::MD_TAG_ADD_TO_WISHLIST,
                'firingTriggerId' => [
                    $triggers[self::MD_ADD_TO_WISHLIST]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'add_to_wishlist'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_ADD_TO_COMPARE => [
                'name' => self::MD_TAG_ADD_TO_COMPARE,
                'firingTriggerId' => [
                    $triggers[self::MD_ADD_TO_COMPARE]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'add_to_compare'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_VIEW_CART => [
                'name' => self::MD_TAG_VIEW_CART,
                'firingTriggerId' => [
                    $triggers[self::MD_VIEW_CART]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'view_cart'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
             self::MD_TAG_ADD_PAYMENT_INFO => [
                'name' => self::MD_TAG_ADD_PAYMENT_INFO,
                'firingTriggerId' => [
                    $triggers[self::MD_ADD_PAYMENT_INFO]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'add_payment_info'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'payment_type'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PAYMENT_TYPE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_ADD_SHIPPING_INFO => [
                'name' => self::MD_TAG_ADD_SHIPPING_INFO,
                'firingTriggerId' => [
                    $triggers[self::MD_ADD_SHIPPING_INFO]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'add_shipping_info'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'shipping_tier'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_SHIPPING_TIER. '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_ITEM_REMOVE_FROM_CART => [
                'name' => self::MD_TAG_ITEM_REMOVE_FROM_CART,
                'firingTriggerId' => [
                    $triggers[self::MD_REMOVE_FROM_CART]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'remove_from_cart'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_ITEM_VIEWS_IMPRESSIONS => [
                'name' => self::MD_ITEM_VIEWS_IMPRESSIONS,
                'firingTriggerId' => [
                    $triggers[self::MD_VIEW_ITEM]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'view_item'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_BEGIN_CHECKOUT => [
                'name' => self::MD_TAG_BEGIN_CHECKOUT,
                'firingTriggerId' => [
                    $triggers[self::MD_BEGIN_CHECKOUT]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'begin_checkout'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'coupon'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_COUPON . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_PURCHASE => [
                'name' => self::MD_TAG_PURCHASE,
                'firingTriggerId' => [
                    $triggers[self::MD_PURCHASE]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'order_count'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ORDER_COUNT . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'all_order_value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ALL_TOTAL_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'purchase'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'transaction_id'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_TRANSACTION_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'affiliation'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_AFFILIATION . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'tax'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_TAX . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'shipping'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_SHIPPING . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'coupon'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_COUPON . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_REFUND => [
                'name' => self::MD_TAG_REFUND,
                'firingTriggerId' => [
                    $triggers[self::MD_REFUND]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'order_count'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ORDER_COUNT . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'all_order_value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ALL_TOTAL_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'refund'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'transaction_id'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_TRANSACTION_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'affiliation'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_AFFILIATION . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'tax'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_TAX . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'shipping'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_SHIPPING . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'coupon'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_COUPON . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'currency'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CURRENCY . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'value'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_REFUND_VALUE . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_VIEW_PROMOTION => [
                'name' => self::MD_TAG_VIEW_PROMOTION,
                'firingTriggerId' => [
                    $triggers[self::MD_VIEW_PROMOTION]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'view_promotion'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'creative_name'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CREATIVE_NAME . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'creative_slot'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CREATIVE_SLOT . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'promotion_id'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PROMOTION_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'promotion_name'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PROMOTION_NAME . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
            self::MD_TAG_SELECT_PROMOTION => [
                'name' => self::MD_TAG_SELECT_PROMOTION,
                'firingTriggerId' => [
                    $triggers[self::MD_SELECT_PROMOTION]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => 'gaawe',
                'parameter' => [
                    [
                        'type' => 'LIST',
                        'key' => 'userProperties',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerGroup'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_GROUP . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'customerId'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CUSTOMER_ID . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => 'select_promotion'
                    ],
                    [
                        'type' => 'LIST',
                        'key' => 'eventParameters',
                        'list' => [
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'items'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PURCHASE_ITEMS . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'creative_name'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CREATIVE_NAME . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'creative_slot'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_CREATIVE_SLOT . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'promotion_id'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PROMOTION_ID . '}}'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'MAP',
                                'map' => [
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'name',
                                        'value' => 'promotion_name'
                                    ],
                                    [
                                        'type' => 'TEMPLATE',
                                        'key' => 'value',
                                        'value' => '{{' . self::MD_VAR_PROMOTION_NAME . '}}'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'TAG_REFERENCE',
                        'key' => 'measurementId',
                        'value' => self::MD_TAG_MEASUREMENT_ID
                    ],
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ]
            ],
        ];

        return $tags;
    }

    /**
     * GetSavedVars
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     */
    protected function getSavedVars($accountId, $containerId)
    {
        $this->limitationOfApi();
        $tokenInfo = json_decode($this->getGTMClient()->getAccessToken());

        try {
            $url=$this->getApiLinkUrl().'accounts/'.$accountId.'/containers/'.$containerId.'/variables';
            //set curl options
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            //get request with url
            $authorization = "Authorization: Bearer " . $tokenInfo->access_token;
            $this->_curl->setCredentials($authorization);
            $this->curl->get($url);
            //read response
            $response = $this->curl->getBody();
            $responseBody = json_decode($response, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on variable listing: ') . $e->getMessage()
            );
        }

        if (isset($responseBody['error'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on variable listing: ') . $responseBody['error']['message']
            );
        }

        $existingData = (isset($responseBody['variables'])) ? $responseBody['variables'] : [];

        return $existingData;
    }

    /**
     * GetAlreadyCreatedTriggers
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     */
    protected function getAlreadyCreatedTriggers($accountId, $containerId)
    {
        $this->limitationOfApi();
        $tokenInfo = json_decode($this->getGTMClient()->getAccessToken());

        try {
            $url=$this->getApiLinkUrl() . 'accounts/' . $accountId . '/containers/' . $containerId . '/triggers';
            //set curl options
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            //get request with url
            $authorization = "Authorization: Bearer " . $tokenInfo->access_token;
            $this->_curl->setCredentials($authorization);
            $this->curl->get($url);
            //read response
            $response = $this->curl->getBody();
            $responseBody = json_decode($response, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on trigger listing: ') . $e->getMessage()
            );
        }

        if (isset($responseBody['error'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on trigger listing: ') . $responseBody['error']['message']
            );
        }

        $existingTriggers = (isset($responseBody['triggers'])) ? $responseBody['triggers'] : [];

        return $existingTriggers;
    }

    /**
     * GetCraetedTag
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     */
    protected function getCraetedTag($accountId, $containerId)
    {
        $this->limitationOfApi();
        $tokenInfo = json_decode($this->getGTMClient()->getAccessToken());

        try {
            $url=$this->getApiLinkUrl() . 'accounts/' . $accountId . '/containers/' . $containerId . '/tags';
            //set curl options
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            //get request with url
            $authorization = "Authorization: Bearer " . $tokenInfo->access_token;
            $this->_curl->setCredentials($authorization);
            $this->curl->get($url);
            //read response
            $response = $this->curl->getBody();
            $responseBody = json_decode($response, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on tag listing: ') . $e->getMessage()
            );
        }

        if (isset($responseBody['error'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on tag listing: ') . $responseBody['error']['message']
            );
        }

        $createdTags = (isset($responseBody['tags'])) ? $responseBody['tags'] : [];

        return $createdTags;
    }

    /**
     * CreateAccountVar
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param Options $options
     */
    protected function createAccountVar($accountId, $containerId, $options)
    {
        $this->limitationOfApi();
        $tokenInfo = json_decode($this->getGTMClient()->getAccessToken());
        $postFields = json_encode($options);

        try {
            $url=$this->getApiLinkUrl() . 'accounts/' . $accountId . '/containers/' . $containerId . '/variables';
            //set curl options
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Content-Length", strlen($postFields));
            //get request with url
            $authorization = "Authorization: Bearer " . $tokenInfo->access_token;
            $this->_curl->setCredentials($authorization);
            $this->curl->post($url, $postFields);
            //read response
            $response = $this->curl->getBody();
            $responseBody = json_decode($response, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on variable creation: ') . $options['name'] . ' '  . $e->getMessage()
            );
        }

        if (isset($responseBody['error'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on variable creation: ') . $options['name'] . ' '  . $responseBody['error']['message']
            );
        }

        return $responseBody;
    }

    /**
     * GetCreatedTrigger
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param Options $options
     */
    protected function getCreateTrigger($accountId, $containerId, $options)
    {
        $this->limitationOfApi();
        $tokenInfo = json_decode($this->getGTMClient()->getAccessToken());
        $postFields = json_encode($options);

        try {
            $url=$this->getApiLinkUrl() . 'accounts/' . $accountId . '/containers/' . $containerId . '/triggers';
            //set curl options
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Content-Length", strlen($postFields));
            //get request with url
            $authorization = "Authorization: Bearer " . $tokenInfo->access_token;
            $this->_curl->setCredentials($authorization);
            $this->curl->post($url, $postFields);
            //read response
            $response = $this->curl->getBody();

            $responseBody = json_decode($response, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on trigger creation:') . $options['name'] . ' '  . $e->getMessage()
            );
        }

        if (isset($responseBody['error'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on trigger creation: ') . $options['name'] . ' '  . $responseBody['error']['message']
            );
        }

        return $responseBody;
    }

    /**
     * GetTagsCreated
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param Options $options
     */
    protected function getTagsCreated($accountId, $containerId, $options)
    {
        $this->limitationOfApi();
        $tokenInfo = json_decode($this->getGTMClient()->getAccessToken());
        $postFields = json_encode($options);

        try {
            $url=$this->getApiLinkUrl() . 'accounts/' . $accountId . '/containers/' . $containerId . '/tags';
            //set curl options
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Content-Length", strlen($postFields));
            //get request with url
            $authorization = "Authorization: Bearer " . $tokenInfo->access_token;
            $this->_curl->setCredentials($authorization);
            $this->curl->post($url, $postFields);
            //read response
            $response = $this->curl->getBody();
            $responseBody = json_decode($response, true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on tag creation:') . $options['name'] . ' '  . $e->getMessage()
            );
        }

        if (isset($responseBody['error'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Api error on tag creation: ') . $options['name'] . ' '  . $responseBody['error']['message']
            );
        }

        return $responseBody;
    }
}
