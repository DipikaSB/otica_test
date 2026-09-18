<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Controller\Adminhtml\Gtag;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var $storedData
     */
    protected $storedData;

    /**
     * @var $resultPageFactory
     */
    public $resultPageFactory;

     /**
      * Construct
      *
      * @param Context $context
      * @param PageFactory $resultPageFactory
      */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Ga4::ga4');
        $resultPage->addBreadcrumb(__('Realtime Tracking Logs'), __('Realtime Tracking Logs'));
        $resultPage->getConfig()->getTitle()->prepend(__('Realtime Tracking Logs'));
        return $resultPage;
    }

    /**
     * IsAllowed
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Ga4::ga4');
    }
}
