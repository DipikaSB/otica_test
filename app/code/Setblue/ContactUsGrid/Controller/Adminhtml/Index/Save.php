<?php

namespace Setblue\ContactUsGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Setblue\ContactUsGrid\Model\ContactUsGrid;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * @var ContactUsGrid
     */
    protected $contactform;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ContactUsGrid $contactform
     */
    public function __construct(
        Context $context,
        ContactUsGrid $contactform,
    ) {
        parent::__construct($context);
        $this->contactform = $contactform;
    }

    /**
     * Execute action
     *
     * @return Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->contactform->load($id);
            }

            if (isset($data['store_id']) && is_array($data['store_id'])) {
                $values = $data['store_id'];
                $data['store_id'] = implode(',', $values);
            }

            $this->contactform->setData($data);

            try {
                $contactFormData = $this->contactform->save();
                $contactFormId = $contactFormData->getId();
                $this->messageManager->addSuccess(__('Contact Form data has been save successfully.'));

                $buttondata = $this->getRequest()->getParam('back');
                if ($buttondata == 'add') {

                    return $resultRedirect->setPath('*/*/add');
                }
                if ($buttondata == 'close') {

                    return $resultRedirect->setPath('*/*/index');
                }

                return $resultRedirect->setPath('*/*/add', ['id' => $contactFormId]);

            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check if the current user has permission to access the "Save Contact Form" functionality.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Setblue_ContactUsGrid::save_contactform');
    }
}
