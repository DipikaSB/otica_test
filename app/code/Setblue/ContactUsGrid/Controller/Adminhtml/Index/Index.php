<?php

namespace Setblue\ContactUsGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
  /**
   * @var PageFactory
   */
    protected $resultPageFactory = false;

  /**
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
   * Execute action
   *
   * @return PageFactory
   */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Contact Form')));

        return $resultPage;
    }

    /**
     * Check if the current user has permission to access the "View Contact Form" functionality.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Setblue_ContactUsGrid::view_contactform');
    }
}
