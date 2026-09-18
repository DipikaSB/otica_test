<?php

namespace Setblue\ContactUsGrid\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Setblue\ContactUsGrid\Model\ContactUsGrid;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;

class Add extends Action
{
    /**
     * @var ContactUsGrid
     */
    protected $contactUsGrid;

    /**
     * Constructor for add or edit action.
     *
     * @param Context $context
     * @param ContactUsGrid $contactUsGrid
     */
    public function __construct(
        Context $context,
        ContactUsGrid $contactUsGrid
    ) {
        parent::__construct($context);
        $this->contactUsGrid = $contactUsGrid;
    }

    /**
     * Execute action
     *
     * @return Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($id && !$this->isIdExists($id)) {
            $this->messageManager->addError(__('Invalid ID. Please check your input.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        } elseif ($id) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Record'));
        } else {
            $resultPage->getConfig()->getTitle()->set(__('Add New Record'));
        }

        return $resultPage;
    }

    /**
     * Check if the ID exists in the custom table
     *
     * @param int $id
     * @return bool
     */
    protected function isIdExists($id)
    {
        $contactUsGrid = $this->contactUsGrid->load($id);
        return $contactUsGrid->getId() ? true : false;
    }

    /**
     * Check if the current user has permission to access the "Add Contact Form" functionality.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Setblue_ContactUsGrid::add_contactform');
    }
}
