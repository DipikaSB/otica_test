<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\TagManager;

use Magento\Framework\View\Element\Template\Context;
use Magedelight\Ga4\Model\DataSaving;
use Magedelight\Ga4\Helper\Data;
use Magedelight\Ga4\Model\CookieData;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Manager extends Template
{
    /**
     * @var \Magedelight\Ga4\Model\DataSaving
     */
    protected $datasaving;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Ga4\Model\CookieData
     */
    protected $storedData;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

     /**
      * Construct
      *
      * @param Context $context
      * @param DataSaving $datasaving
      * @param Data $helper
      * @param CookieData $storedData
      * @param CollectionFactory $orderCollectionFactory
      * @param array $data
      */

    public function __construct(
        Context $context,
        DataSaving $datasaving,
        Data $helper,
        CookieData $storedData,
        CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->datasaving = $datasaving;
        $this->helper = $helper;
        $this->storedData = $storedData;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * IsGTMStatus
     */
    public function isGTMStatus()
    {
        return $this->helper->isGTMStatus();
    }

    /**
     * SetTriggerEvent
     *
     * @param Label $label
     * @param Value $value
     */
    public function setTriggerEvent($label, $value)
    {
        $itemPurchasesData = $this->getItemPurchases();
        if (!$itemPurchasesData) {
            $itemPurchasesData = [];
        }
        $itemPurchasesData[$label] = $value;

        $this->setEventValues('ecommerce', $itemPurchasesData);
        return $this;
    }

    /**
     * SetTriggerEvent
     *
     * @param Label $label
     */
    public function getItemPurchases($label = null)
    {
        $itemPurchasesData = $this->getEventContent('item');
        if (($label)) {
            return $itemPurchasesData[$label];
        }

        return $itemPurchasesData;
    }

    /**
     * GetJsonFormatContent
     */
    public function getJsonFormatContent()
    {
        return json_encode($this->getEventContent());
    }

    /**
     * SetEventDataLayer
     *
     * @param Label $label
     * @param Value $value
     */
    public function setEventDataLayer($label, $value)
    {
        return $this->datasaving->setData($label, $value);
    }

    /**
     * GetEventContent
     *
     * @param Label $label
     */
    public function getEventContent($label = null)
    {
        if ($label) {
            return $this->datasaving->getData($label);
        }

        return $this->datasaving->getData();
    }

    /**
     * SetEventValues
     *
     * @param Label $label
     * @param Value $value
     */
    public function setEventValues($label, $value)
    {
        $this->datasaving->setData($label, $value);
        return $this;
    }

    /**
     * SetPurchaseData
     *
     * @param Label $label
     * @param Value $value
     */
    public function setPurchaseData($label, $value)
    {
        $ecommerceData = $this->getEcommerceData();
        if (!$ecommerceData) {
            $ecommerceData = [];
        }
        $ecommerceData[$label] = $value;

        $this->setEventValues('ecommerce', $ecommerceData);
        return $this;
    }

    /**
     * SetPurchaseData
     *
     * @param mixed $ecommerceData
     * @return Manager
     */
    public function setOrderPurchaseData($ecommerceData)
    {
        $this->setEventValues('ecommerce', $ecommerceData);
        return $this;
    }

    /**
     * SetPurchaseData
     *
     * @param mixed $ecommerceData
     * @return Manager
     */
    public function setCheckoutPurchaseData($ecommerceData)
    {
        $this->setEventValues('ecommerce', $ecommerceData);
        return $this;
    }

    /**
     * GetEcommerceData
     *
     * @param Label $label
     */
    public function getEcommerceData($label = null)
    {
        $ecommerceData = $this->getStoredEventData('ecommerce');
        if (isset($label)) {
            return $ecommerceData[$label];
        }

        return $ecommerceData;
    }

    /**
     * GetJsonDataEventLayer
     *
     * @param Label $label
     */
    public function getJsonDataEventLayer()
    {
        $options = $this->getStoredEventData();
        $options = $this->breakContent($options);

        return json_encode($options);
    }

    /**
     * GetStoredEventData
     *
     * @param Label $label
     */
    public function getStoredEventData($label = null)
    {
        if ($label) {
            return $this->datasaving->getData($label);
        }

        return $this->datasaving->getData();
    }

    /**
     * BreakContent
     *
     * @param Options $options
     */
    private function breakContent($options)
    {

        $result = [];
        $chunkLimit = 1;

        if (isset($options['ecommerce']['impressions'])) {
            $currencyCode = $options['ecommerce']['currencyCode'];
            $eventLabel = '';
            if (isset($options['eventLabel'])) {
                $eventLabel = $options['eventLabel'];
            }
            $originalImpressions = $options['ecommerce']['impressions'];
            $impressionsCount = count($originalImpressions);
            if ($impressionsCount <= $chunkLimit) {
                $result[] = $options;
                return $result;
            }

            $impressionChunks = array_chunk($originalImpressions, $chunkLimit);
            $options['ecommerce']['impressions'] = $impressionChunks[0];
            $result[] = $options;

            $chunkCount = count($impressionChunks);
            for ($i = 1; $i<$chunkCount; $i++) {
                $newImpressionChunk = [];
                $newImpressionChunk['ecommerce'] = [];
                $newImpressionChunk['ecommerce']['currencyCode'] = $currencyCode;
                $newImpressionChunk['ecommerce']['impressions'] = $impressionChunks[$i];

                $newImpressionChunk['event'] = 'impression';
                $newImpressionChunk['eventCategory'] = 'Ecommerce';
                $newImpressionChunk['eventAction'] = 'Impression';
                $newImpressionChunk['eventLabel'] = $eventLabel;

                $result[] = $newImpressionChunk;
            }

            return $result;
        } else {
            $result[] = $options;
            return $result;
        }
    }

    /**
     * GetCookiesForEventJsCode
     */
    public function getCookiesForEventJsCode()
    {
        $savedData = $this->storedData->getStoreCookies();
        return implode(',', array_map(function ($t) {
                return "'" . $t . "'";
        }, $savedData));
    }

    /**
     * DataLayerOption
     *
     * @param layerContent $layerContent
     */
    public function dataLayerOption($layerContent)
    {
        $layers = $this->datasaving->getData('additional_datalayer_option');
        if (!$layers) {
            $layers = [];
        }
        $layers[] = $layerContent;
        $this->datasaving->setData('additional_datalayer_option', $layers);
        return $this;
    }

    /**
     * Data Helper.
     *
     * @return Data
     */
    public function dataHelper()
    {
        return $this->helper;
    }
}
