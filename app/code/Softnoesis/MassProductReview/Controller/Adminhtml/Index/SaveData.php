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
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\DecoderInterface;

class SaveData extends \Magento\Backend\App\Action
{
    protected $uploaderFactory;
    protected $allowedExtensions = ['csv'];
    protected $csv;
    protected $fileId = 'import_file';
    protected $ConverterToArray;
    protected $fileSystem;
    protected $_reviewFactory;
    protected $_ratingFactory;
    protected $_storeManager;
    /**
     * @var EncoderInterface
     */
    protected $urlEncode;
    /**
     * @var DecoderInterface
     */
    protected $urlDecode;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\Csv $csv,
        \Softnoesis\MassProductReview\Model\Block\ConverterToArray $ConverterToArray,
        UploaderFactory $uploaderFactory,
        Filesystem $fileSystem,
        EncoderInterface $urlEncode,
        DecoderInterface $urlDecode
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->csv = $csv;
        $this->ConverterToArray = $ConverterToArray;
        $this->fileSystem = $fileSystem;
        $this->urlEncode = $urlEncode;
        $this->urlDecode = $urlDecode;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $files = $this->getRequest()->getParams();
            $filePath = $this->urlDecode->decode($files['path']);
            $fileName = $filePath.$files['name'] ;
            if ($fileName) {
                $file = $fileName;
                $this->getMassProductReview($file);
            } else {
                $this->messageManager->addError(__("Please Upload CSV file"));
            }
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
    public function getMassProductReview()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/MassProductReview.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $files = $this->getRequest()->getParams();
        $filePath = $this->urlDecode->decode($files['path']);
        $fileName = $filePath . $files['name'];

        if ($files['size'] && $fileName) {

            $rows = $this->csv->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {

                $data = [];

                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }

                $reviewdata = $this->ConverterToArray->convertRow($data);

                foreach ($reviewdata as $reviewItem) {

                    $productId = $reviewItem['Product Id'];

                    $reviewFinalData = [];
                    $reviewFinalData['ratings'][4] = $reviewItem['rating'];
                    $reviewFinalData['nickname'] = $reviewItem['Nick Name'];
                    $reviewFinalData['title'] = $reviewItem['Review Summary'];
                    $reviewFinalData['detail'] = $reviewItem['Review Detail'];
                    $reviewFinalData['customer_id'] = null;

                    //=========================================
                    // Review Images
                    //=========================================
                    $imagePath = null;
                    $imagePaths = [];

                    if (!empty($reviewItem['Review Image'])) {

                        $images = explode(',', $reviewItem['Review Image']);

                        $mediaDirectory = $this->fileSystem->getDirectoryWrite(
                            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
                        );

                        if (!$mediaDirectory->isExist('review/image')) {
                            $mediaDirectory->create('review/image');
                        }

                        foreach ($images as $image) {

                            $imageName = trim($image);

                            if (empty($imageName)) {
                                continue;
                            }

                            $sourceFile = BP
                                . DIRECTORY_SEPARATOR . 'var'
                                . DIRECTORY_SEPARATOR . 'import'
                                . DIRECTORY_SEPARATOR . 'images'
                                . DIRECTORY_SEPARATOR . $imageName;

                            $logger->info('Checking: ' . $sourceFile);

                            if (file_exists($sourceFile)) {

                                $destination = 'review/image/' . $imageName;

                                $mediaDirectory->writeFile(
                                    $destination,
                                    file_get_contents($sourceFile)
                                );

                                $imagePaths[] = $destination;

                                $logger->info('Copied: ' . $destination);

                            } else {

                                $logger->info('Image not found: ' . $sourceFile);
                            }
                        }

                        if (!empty($imagePaths)) {
                            $imagePath = implode(',', $imagePaths);
                        }
                    }

                    //=========================================
                    // Save Review
                    //=========================================
                    $review = $this->_reviewFactory->create()->setData($reviewFinalData);

                    $review->unsetData('review_id');

                    $review->setEntityId(
                        $review->getEntityIdByCode(
                            \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE
                        )
                    )
                    ->setEntityPkValue($productId)
                    ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                    ->setReviewImage($imagePath)
                    ->setStoreId($this->_storeManager->getStore()->getId())
                    ->setStores([$this->_storeManager->getStore()->getId()])
                    ->save();

                    //=========================================
                    // Ratings
                    //=========================================
                    foreach ($reviewFinalData['ratings'] as $ratingId => $value) {

                        if ($value) {

                            $ratingModel = $this->_ratingFactory
                                ->create()
                                ->load($ratingId);

                            foreach ($ratingModel->getOptions() as $option) {

                                if ((int)$option->getValue() === (int)$value) {

                                    $ratingModel->setRatingId($ratingId)
                                        ->setReviewId($review->getId())
                                        ->addOptionVote($option->getId(), $productId);

                                    break;
                                }
                            }
                        }
                    }

                    $review->aggregate();
                }
            }

            $this->messageManager->addSuccessMessage(__('Import Data successfully'));

        } else {

            $this->messageManager->addErrorMessage(__('File is empty'));
        }
    }
}
