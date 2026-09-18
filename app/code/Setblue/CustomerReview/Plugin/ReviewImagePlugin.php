<?php
namespace Setblue\CustomerReview\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\ReviewFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Exception\LocalizedException;

class ReviewImagePlugin
{
    protected $request;
    protected $filesystem;
    protected $reviewFactory;
    protected $reviewResource;
    protected $uploaderFactory;
    protected $fileIo;
    protected $adapterFactory;

    public function __construct(
        RequestInterface $request,
        Filesystem $filesystem,
        ReviewFactory $reviewFactory,
        ReviewResource $reviewResource,
        UploaderFactory $uploaderFactory,
        File $fileIo,
        AdapterFactory $adapterFactory
    ) {
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->reviewFactory = $reviewFactory;
        $this->reviewResource = $reviewResource;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileIo = $fileIo;
        $this->adapterFactory = $adapterFactory;
    }

    public function afterExecute(
        \Magento\Review\Controller\Product\Post $subject,
        $result
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/SBImagesave.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        try {
            $requestParams = $subject->getRequest()->getFiles();
            $logger->info('Request Params: ' . print_r($requestParams, true));
            $logger->info('FILES: ' . print_r($_FILES, true));

            if (isset($_FILES['image']) && isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

                $imageAdapter = $this->adapterFactory->create();
                $uploader->addValidateCallback('custom_image_upload', $imageAdapter, 'validateUploadFile');

                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('review/images');

                $resultUpload = $uploader->save($destinationPath);

                if (!$resultUpload) {
                    throw new LocalizedException(__('File cannot be saved to path: %1', $destinationPath));
                }

                $imagePath = 'review/images' . $resultUpload['file'];
                $filename = $resultUpload['file'];

                $reviewData = $subject->getRequest()->getPostValue();
                $productId = $reviewData['product_id'] ?? null;

                if ($productId) {
                    $review = $this->reviewFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('entity_pk_value', $productId)
                        ->setOrder('review_id', 'DESC')
                        ->getFirstItem();

                    if ($review && $review->getId()) {
                        $review->setData('review_image', $imagePath);
                        $this->reviewResource->save($review);
                    } else {
                        $logger->info('No matching review found for product ID: ' . $productId);
                    }
                } else {
                    $logger->info('Product ID not found in review data.');
                }
            } else {
                $logger->info('No image uploaded in request.');
            }
        } catch (\Exception $e) {
            $logger->err('Exception: ' . $e->getMessage());
        }

        return $result;
    }
}
