<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Controller\Adminhtml\Event;

use \Magedelight\Ga4\Model\Events;
use \Magedelight\Ga4\Model\CreatedData;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;

class Generate extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magedelight\Ga4\Model\Events
     */
    protected $eventsModelData = null;

    /**
     * @var \Magedelight\Ga4\Model\CreatedData
     */
    protected $modelJson;

    /**
     * Construct
     *
     * @param Events $eventsModelData
     * @param CreatedData $modelJson
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Events $eventsModelData,
        CreatedData $modelJson,
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        $this->eventsModelData = $eventsModelData;
        $this->modelJson = $modelJson;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     * phpcs:disable
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $msg = $this->_validateParams($params);
        $apiOptions = ['variables', 'triggers', 'tags'];

        if (!count($msg)) {
            foreach ($apiOptions as $option) {
                try {
                    $result = $this->eventsModelData->
                    drawEventItems(
                        $option,
                        $params['account_id'],
                        $params['container_id'],
                        $params['tracking_id'],
                        $params['ipaddress'],
                        $params['display_advertising']
                    );
                    $msg = array_merge($msg, $result);
                } catch (\Exception $ex) {
                    $msg[] = $ex->getMessage();
                }
            }
        }

        if (!count($msg)) {
            $msg[] = __('Nothing was done, items were already created.');
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($msg);
        return $resultJson;
    }

    /**
     * ValidateParams
     *
     * @param Params $params
     */
    protected function _validateParams($params)
    {
        $trakingId   = $params['tracking_id'];
        $containerId    = $params['container_id'];
        $accountId      = $params['account_id'];

        $message = [];
        if (!strlen(trim($trakingId))) {
            $message[] = __('Universal Tracking Id must be specified');
        }

        if (!strlen(trim($containerId))) {
            $message[] = __('Container Id must be specified');
        }

        if (!strlen(trim($accountId))) {
            $message[] = __('Account Id must be specified');
        }
        return $message;
    }
}
