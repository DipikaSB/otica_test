<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Controller\Adminhtml\Index;

class Delete extends \Setblue\CustomerWholesaleInquiry\Controller\Adminhtml\Index
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Setblue\Asktoexpert\Model\WholesaleInquiry::class);
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('wholesaleinquiry/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_redirect('wholesaleinquiry/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('wholesaleinquiry/*/');
    }
}
