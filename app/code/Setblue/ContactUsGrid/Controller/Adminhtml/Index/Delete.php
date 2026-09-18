<?php

namespace Setblue\ContactUsGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Setblue\ContactUsGrid\Model\ContactUsGrid;

class Delete extends Action
{
    /**
     * @var ContactUsGrid
     */
    protected $contactform;

    /**
     * @param Context $context
     * @param ContactUsGrid $contactform
     */
    public function __construct(
        Context $context,
        ContactUsGrid $contactform
    ) {
        parent::__construct($context);
        $this->contactform = $contactform;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->contactform;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Contact Form data deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/add', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('Contact Form data does not exist.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check if the current user has permission to access the "Delete Contact Form" functionality.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Setblue_ContactUsGrid::delete_contactform');
    }
}
