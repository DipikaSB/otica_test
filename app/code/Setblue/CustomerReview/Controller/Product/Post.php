<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Setblue\CustomerReview\Controller\Product;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Setblue\CustomerReview\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;

class Post extends ProductController implements HttpPostActionInterface
{
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $data = $this->reviewSession->getFormData(true);
        $rating = [];

        if ($data) {
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPostValue();
            $rating = $this->getRequest()->getParam('ratings', []);
        }

        $imagePath = null;

        if (($product = $this->initProduct()) && !empty($data)) {
            /** @var \Magento\Review\Model\Review $review */
            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id');

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    // Handle image/video upload
                    $imageData = $this->getRequest()->getFiles();
                    if (isset($imageData['review_image']['name']) && $imageData['review_image']['name'] !== '') {
                        try {
                            $uploader = $this->uploaderFactory->create(['fileId' => 'review_image']);
                            $allowedExtensions = [
                                'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg',
                                'mp4', 'mov', 'avi', 'flv', 'wmv', 'mkv', 'webm', 'm4v', '3gp'
                            ];
                            $uploader->setAllowedExtensions($allowedExtensions);

                            // Get file extension
                            $fileExtension = strtolower(pathinfo($imageData['review_image']['name'], PATHINFO_EXTENSION));

                            // Only validate images with image adapter
                            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'])) {
                                $imageAdapter = $this->adapterFactory->create();
                                $uploader->addValidateCallback('custom_image_upload', $imageAdapter, 'validateUploadFile');
                            }

                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(true);

                            // Validate video file size (max 5MB)
                            $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
                            $fileSize = $imageData['review_image']['size'];

                            if (in_array($fileExtension, ['mp4', 'mov', 'avi', 'flv', 'wmv', 'mkv', 'webm', 'm4v', '3gp']) && $fileSize > $maxFileSize) {

                                throw new LocalizedException(__('The file size should not exceed 5MB.'));
                            }

                            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                            $destinationPath = $mediaDirectory->getAbsolutePath('review/image');
                            $result = $uploader->save($destinationPath);

                            if (!$result) {
                                throw new LocalizedException(__('File cannot be saved to path: %1', $destinationPath));
                            }

                            $imagePath = 'review/image' . $result['file'];
                        } catch (LocalizedException $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                            $redirectUrl = $this->reviewSession->getRedirectUrl(true);
                            $resultRedirect->setUrl($redirectUrl ?: $this->_redirect->getRedirectUrl());
                            return $resultRedirect;
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__('Review has not been submitted. Please try again.'));
                            $redirectUrl = $this->reviewSession->getRedirectUrl(true);
                            $resultRedirect->setUrl($redirectUrl ?: $this->_redirect->getRedirectUrl());
                            return $resultRedirect;
                        }
                    }

                    $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Review::STATUS_PENDING)
                        ->setReviewImage($imagePath)
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($this->customerSession->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $this->messageManager->addSuccessMessage(__('You submitted your review for moderation.'));
                } catch (\Exception $e) {
                    $this->reviewSession->setFormData($data);
                    $this->messageManager->addErrorMessage(__('We can\'t post your review right now.'));
                }
            } else {
                $this->reviewSession->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('We can\'t post your review right now.'));
                }
            }
        }

        $redirectUrl = $this->reviewSession->getRedirectUrl(true);
        $resultRedirect->setUrl($redirectUrl ?: $this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
