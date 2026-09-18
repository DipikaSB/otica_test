<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Controller\Adminhtml\Data;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magedelight\Ga4\Model\CreatedData;

class Export extends Action
{
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $http;

    /**
     * @var \Magedelight\Ga4\Model\CreatedData
     */
    protected $jsonGenerator;

     /**
      * Construct
      *
      * @param Context $context
      * @param Http $http
      * @param CreatedData $jsonGenerator
      */
    public function __construct(
        Context $context,
        Http $http,
        CreatedData $jsonGenerator
    ) {
        parent::__construct($context);
        $this->http = $http;
        $this->jsonGenerator = $jsonGenerator;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $response = $this->jsonGenerator->getCreatedJsonData();
        $this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json')
            ->setHeader("Content-Disposition", "attachment; filename=ga4Export.json")
            ->setBody($response);
    }
}
