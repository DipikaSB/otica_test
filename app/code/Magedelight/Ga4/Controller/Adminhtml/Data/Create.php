<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Controller\Adminhtml\Data;

class Create extends \Magedelight\Ga4\Controller\Adminhtml\Event\Generate
{
    /**
     * Execute
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $jsonUrl = null;
        $msg = $this->_validateParams($params);

        if (!count($msg)) {
            try {
                $jsonUrl = $this->modelJson->
                generateItemJson(
                    trim($params['account_id']),
                    trim($params['container_id']),
                    trim($params['tracking_id']),
                    trim($params['public_id'])
                );
                $msg[]=__('File successfully generated.You can download the file by clicking on the Download button.');
            } catch (\Exception $ex) {
                $msg[] = $ex->getMessage();
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'msg' => $msg,
            'jsonUrl' => $jsonUrl
        ]);
        return $resultJson;
    }

    /**
     * ValidateParams
     *
     * @param Params $params
     */
    protected function _validateParams($params)
    {
        $accountId = $params['account_id'];
        $containerId = $params['container_id'];
        $trackingId = $params['tracking_id'];
        $publicId = $params['public_id'];

        $message = [];

        if (!strlen(trim($accountId))) {
            $message[] = __('Account ID must be specified');
        }

        if (!strlen(trim($containerId))) {
            $message[] = __('Container ID must be specified');
        }

        if (!strlen(trim($trackingId))) {
            $message[] = __('Universal Tracking ID must be specified');
        }

        if (!strlen(trim($publicId))) {
            $message[] = __('Public ID must be specified');
        }

        return $message;
    }
}
