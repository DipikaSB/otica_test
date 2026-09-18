<?php

namespace Setblue\Warranty\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{

    private $resultPageFactory;
    protected $VendorFactory;
   
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Warranty Enquiry"));
            return $resultPage;
    }
}
