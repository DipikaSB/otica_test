<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class CreatedData extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var $jsonFileName
     */
    protected $jsonFileName;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $displayAdvertising;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ipaddress;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $publicId;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $Events;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $fingerprint;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $accountId;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $containerId;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $trackingId;

    /**
     * @param Events $events
     * @param Filesystem $filesystem
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        \Magedelight\Ga4\Model\Events $events,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->filesystem = $filesystem;
        $this->events = $events;
        $this->jsonFileName = 'ga4Export' . DIRECTORY_SEPARATOR . 'ga4.json';
    }

    /**
     * GenerateItemJson
     *
     * @param AccountId $accountId
     * @param ContainerId $containerId
     * @param TrackingId $trackingId
     * @param PublicId $publicId
     */
    public function generateItemJson($accountId, $containerId, $trackingId, $publicId)
    {
        $this->publicId = $publicId;
        $this->fingerprint = time();
        $this->accountId = $accountId;
        $this->containerId = $containerId;
        $this->trackingId = $trackingId;

        $jsonData = $this->getMakingJsonData();
        $triggerData = $this->getTriggerData();
        $tagsData = $this->getTagsData($triggerData);

        $values = [
            "path" => "accounts/$this->accountId/containers/$this->containerId/versions/0",
            "accountId" => $this->accountId,
            "containerId" => $this->containerId,
            "containerVersionId" => "0",
            "fingerprint" => $this->fingerprint,
            "tagManagerUrl"=>"https://tagmanager.google.com/#/versions/accounts/$this->accountId/containers/$this->containerId/versions/0?apiLink=version", //phpcs:ignore
            "container" => [
                "path" => "accounts/$this->accountId/containers/$this->containerId",
                "accountId" => $this->accountId,
                "containerId" => $this->containerId,
                "name" => "Magedelight_Ga4_JsonExport",
                "publicId" => $this->publicId,
                "usageContext" => [
                    "WEB"
                ],
                "fingerprint" => $this->fingerprint,
                "tagManagerUrl" => "https://tagmanager.google.com/#/container/accounts/$this->accountId/containers/$this->containerId/workspaces?apiLink=container" //phpcs:ignore
            ],
            "builtInVariable" => [
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "PAGE_URL",
                    "name" => "Page URL"
                ],
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "PAGE_HOSTNAME",
                    "name" => "Page Hostname"
                ],
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "PAGE_PATH",
                    "name" => "Page Path"
                ],
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "REFERRER",
                    "name" => "Referrer"
                ],
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "EVENT",
                    "name" => "Event"
                ],
            ],
            "variable" => array_values($jsonData),
            "trigger" => array_values($triggerData),
            "tag" => array_values($tagsData)
        ];

        $options = [
            "exportFormatVersion" => 2,
            "exportTime" => date("Y-m-d h:i:s"),
            "containerVersion" => $values
        ];

        $exportJosnDataDr = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $exportJosnDataDr->writeFile(
            $this->jsonFileName,
            json_encode(
                $options,
                JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            )
        );
        return true;
    }

    /**
     * GetCreatedJsonData
     */
    public function getCreatedJsonData()
    {
        $exportJosnDataDr = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        return $exportJosnDataDr->readFile($this->jsonFileName);
    }

    /**
     * GetMakingJsonData
     */
    protected function getMakingJsonData()
    {
        $newVarList = $this->events->getVariablesList($this->trackingId);

        $variableId = 1;
        foreach ($newVarList as $variableName => &$optionOfVars) {
            if (isset($optionOfVars['parameter'])) {
                foreach ($optionOfVars['parameter'] as &$paramOptions) {
                    if (isset($paramOptions['type'])) {
                        $paramOptions['type'] = strtoupper($paramOptions['type']);
                    }
                }
            }

            $optionOfVars['accountId'] = $this->accountId;
            $optionOfVars['containerId'] = $this->containerId;
            $optionOfVars['variableId'] = $variableId;
            $optionOfVars['fingerprint'] = $this->fingerprint;
            ++$variableId;
        }

        return $newVarList;
    }

    /**
     * GetTriggerData
     * phpcs:disable
     */
    protected function getTriggerData()
    {
        $triggerList = $this->events->getTriggersList();
        $triggerId = 1;
        foreach ($triggerList as $triggerName => &$optionOfTriggers) {
            if (isset($optionOfTriggers['customEventFilter'])) {
                foreach ($optionOfTriggers['customEventFilter'] as &$eventOption) {
                    if (isset($eventOption['parameter'])) {
                        foreach ($eventOption['parameter'] as &$paramOptions) {
                            if (isset($paramOptions['type'])) {
                                $paramOptions['type'] = strtoupper($paramOptions['type']);
                            }
                        }
                    }
                    if (isset($eventOption['type'])) {
                        if (isset($eventOption['type'])) {
                            $eventOption['type'] = strtoupper($eventOption['type']);
                        }
                    }
                }
            }
            if (isset($optionOfTriggers['filter'])) {
                foreach ($optionOfTriggers['filter'] as &$filterOptions) {
                    if (isset($filterOptions['parameter'])) {
                        foreach ($filterOptions['parameter'] as &$paramOptions) {
                            if (isset($paramOptions['type'])) {
                                $paramOptions['type'] = strtoupper($paramOptions['type']);
                            }
                        }
                    }
                    if (isset($filterOptions['type'])) {
                        if (isset($filterOptions['type'])) {
                            $filterOptions['type'] = strtoupper($filterOptions['type']);
                        }
                    }
                }
            }
            if (isset($optionOfTriggers['type'])) {
                $optionOfTriggers['type']=strtoupper(preg_replace('/(.)([A-Z])/', '$1_$2', $optionOfTriggers['type']));
            }

            $optionOfTriggers['accountId'] = $this->accountId;
            $optionOfTriggers['containerId'] = $this->containerId;
            $optionOfTriggers['triggerId'] = $triggerId;
            $optionOfTriggers['fingerprint'] = $this->fingerprint;
            $triggerId+=1;
        }

        return $triggerList;
    }

    /**
     * GetTagsData
     * phpcs:disable
     * @param Triggers $triggers
     */
    public function getTagsData($triggers)
    {
        $map = [];

        foreach ($triggers as $trigger) {
            $map[$trigger['name']] = $trigger['triggerId'];
        }
        $tagsListForCreate = $this->events->getTagsList($map);

        $tagId = 1;
        foreach ($tagsListForCreate as $tagName => &$tagListOptions) {
            if (isset($tagListOptions['parameter'])) {
                foreach ($tagListOptions['parameter'] as $key => &$paramOptions) {
                    if (empty($paramOptions)) {
                        unset($tagListOptions['parameter'][$key]);
                        continue;
                    }
                    $paramOptions['type'] = strtoupper($paramOptions['type']);
                    if (isset($paramOptions['list'])) {
                        foreach ($paramOptions['list'] as &$listOptions) {
                            $listOptions["type"] = strtoupper($listOptions["type"]);
                            foreach ($listOptions["map"] as &$mapOptions) {
                                $mapOptions['type'] = strtoupper($mapOptions['type']);
                            }
                        }
                    }
                }
            }
            if (isset($tagListOptions['tagFiringOption'])) {
                $tagListOptions['tagFiringOption']=strtoupper(
                    preg_replace('/(.)([A-Z])/', '$1_$2', $tagListOptions['tagFiringOption'])
                );
            }

            $tagListOptions['accountId'] = $this->accountId;
            $tagListOptions['containerId'] = $this->containerId;
            $tagListOptions['tagId'] = $tagId;
            $tagListOptions['fingerprint'] = $this->fingerprint;
            $tagId+=1;
        }

        return $tagsListForCreate;
    }
}
