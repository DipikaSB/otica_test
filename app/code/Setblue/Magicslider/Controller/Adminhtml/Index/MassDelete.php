<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Controller\Adminhtml\Index;

class MassDelete extends \Setblue\Magicslider\Controller\Adminhtml\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $magicsliderIds = $this->getRequest()->getParam('magicslider');
        if (!is_array($magicsliderIds) || empty($magicsliderIds)) {
            $this->messageManager->addError(__('Please select magicslider(s).'));
        } else {
            $collection = $this->_magicsliderCollectionFactory->create()
                ->addFieldToFilter('magicslider_id', ['in' => $magicsliderIds]);
            try {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($magicsliderIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
