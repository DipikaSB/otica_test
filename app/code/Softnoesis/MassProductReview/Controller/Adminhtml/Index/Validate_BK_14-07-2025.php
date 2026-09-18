<?php
/**
 * Softnoesis
 * Copyright(C) 05/2023 Softnoesis <ideveloper1990@gmail.com>
 * @package Softnoesis_MassProductReview
 * @copyright Copyright(C) 2015 Softnoesis (ideveloper1990@gmail.com)
 * @author Softnoesis <ideveloper1990@gmail.com>
 */

declare(strict_types=1);

namespace Softnoesis\MassProductReview\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\DecoderInterface;

class Validate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     */
     protected $uploaderFactory;
     /**
      * @var allowedExtensions
      */
     protected $allowedExtensions = ['csv'];
     /**
      * @var \Magento\Framework\File\Csv
      */
     protected $csv;
     /**
      * @var fileId
      */
    protected $fileId = 'import_file';
    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_mediaDirectory;

    protected $fileSystem;

    /**
     * @var EncoderInterface
     */
    protected $urlEncode;
    /**
     * @var DecoderInterface
     */
    protected $urlDecode;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magedelight\CmsImportExport\Helper\Data $helperData
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList
     * @param EncoderInterface $urlEncode
     * @param DecoderInterface $urlDecode
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\File\Csv $csv,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        Filesystem $fileSystem,
        EncoderInterface $urlEncode,
        DecoderInterface $urlDecode
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->csv = $csv;
        $this->fileSystem = $fileSystem;
        $this->urlEncode = $urlEncode;
        $this->urlDecode = $urlDecode;
        parent::__construct($context);
    }
    /*csv and xml file validation check*/
    public function execute()
    {
       
        try {
            $this->validateCsv();
        } catch (Exception $e) {
                $this->messageManager->addError(__('Not Proper file upload'));
                $this->_redirect('*/*/index');
        }
    }
    public function validateCsv()
    {
        $destinationPath = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getAbsolutePath('/');
        $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions);
            
        $result = $uploader->save($destinationPath);
        
        $filePath = $result['path'].$result['file'];

        $uploadedFile = $uploader->getUploadedFileName();

        $destinationFile = $destinationPath.$uploadedFile;
        
        // Set permissions for the destination file
        chmod($destinationFile, 0777);

        //$file = $this->getRequest()->getFiles('import_file');
        $data = $this->csv->getData($filePath);

        $errors = [];

        foreach ($data as $rowNumber => $rowData) {
            if (count($rowData) !== 12) {
                $errors[] = __('Invalid Row(s) Row %1', $rowNumber + 1);
            } else {
                if (empty($rowData[0]) || empty($rowData[1]) || empty($rowData[2]) || empty($rowData[3]) || empty($rowData[4]) || empty($rowData[5]) || empty($rowData[6]) || empty($rowData[7]) || empty($rowData[8]) || empty($rowData[10]) || empty($rowData[11])) {

                    $errors[] = __('Empty required data in Row(s) %1', $rowNumber + 1);
                    $this->_redirect('*/*/index');

                }
            }
        }
        if (empty($errors)) {
            $import_file = $result['file'];
            $size = $result['size'];
            $path = $this->urlEncode->encode($result['path']);
            $cartLink = $this->getUrl('import/index/SaveData', ['path' => $path ,'name' => $import_file, 'size' => $size]);
            $message =  __('File is valid! To start import process press "Import" button:').'  '.'<div class = "cmsimport"><a  href="'.$cartLink .'">'. __('Import') .'</a></div>';
            $this->messageManager->addSuccess($message);
            $this->_redirect('*/*/index');
        } else {
            foreach ($errors as $error) {
                $this->messageManager->addError($error);
            }
           
            $this->_redirect('*/*/index');
        }
    }
}
