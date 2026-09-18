<?php

namespace Setblue\Warranty\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;

abstract class Index extends \Magento\Backend\App\Action
{
    
    protected $_coreRegistry;

    protected $resultForwardFactory;
    protected $resultPageFactory;

    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;
    protected $directoryList;
    protected $_file;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->_file = $file;
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Setblue_Warranty::Warranty')->_addBreadcrumb(__('Items'), __('Items'));
        return $this;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Setblue_Warranty::Warranty');
    }
}
