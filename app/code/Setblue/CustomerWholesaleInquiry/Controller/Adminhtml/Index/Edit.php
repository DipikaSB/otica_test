<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */

namespace Setblue\CustomerWholesaleInquiry\Controller\Adminhtml\Index;

class Edit extends \Setblue\CustomerWholesaleInquiry\Controller\Adminhtml\Index
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        $model = $this->_objectManager->create(\Setblue\CustomerWholesaleInquiry\Model\WholesaleInquiry::class);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('wholesaleinquiry/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_Setblue_CustomerWholesaleInquiry_items', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
