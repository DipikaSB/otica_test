<?php

namespace Meetanshi\ReviewReminderBasic\Controller\Review;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\ReviewReminderBasic\Helper\Data;

/**
 * Controller for handling review form submission with full validation.
 */
class Submit extends Action implements HttpPostActionInterface
{
    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Submit constructor.
     *
     * @param Context $context
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param FormKeyValidator $formKeyValidator
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        FormKeyValidator $formKeyValidator,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        Data $helper
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute action - validate and save review with duplicate prevention.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        $productId = (int) ($post['product_id'] ?? 0);
        $orderIncrementId = trim($post['order_increment_id'] ?? '');
        $redirectParams = [
            'product_id' => $productId,
            'order' => $orderIncrementId
        ];

        // 1. Validate form key (CSRF protection)
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page and try again.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        // 2. Validate product exists
        if (!$productId) {
            $this->messageManager->addErrorMessage(__('Invalid product.'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        try {
            $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('The requested product is no longer available.'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        // 3. Extract and sanitize form fields
        $nickname = trim($post['nickname'] ?? '');
        $title = trim($post['title'] ?? '');
        $detail = trim($post['detail'] ?? '');
        $ratings = $post['ratings'] ?? [];

        // 4. Validate required fields
        if (empty($nickname) || empty($title) || empty($detail)) {
            $this->messageManager->addErrorMessage(__('Please fill in all required fields.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        // 5. Validate field lengths
        if (mb_strlen($nickname) < 3 || mb_strlen($nickname) > 255) {
            $this->messageManager->addErrorMessage(__('Nickname must be between 3 and 255 characters.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        if (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
            $this->messageManager->addErrorMessage(__('Summary must be between 3 and 255 characters.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        if (mb_strlen($detail) < 10) {
            $this->messageManager->addErrorMessage(__('Review must be at least 10 characters long.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        // 6. Validate at least one rating is selected
        if (empty($ratings)) {
            $this->messageManager->addErrorMessage(__('Please select a star rating.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        // 6a. Validate nickname matches order customer name (prevent fake name reviews)
        $customerNameEncoded = $this->getRequest()->getParam('customer_name', '');
        if (!empty($customerNameEncoded)) {
            $redirectParams['customer_name'] = $customerNameEncoded;
        }
        if (!empty($customerNameEncoded)) {
            $expectedName = base64_decode($customerNameEncoded, true);
            if ($expectedName !== false && strcasecmp(trim($nickname), trim($expectedName)) !== 0) {
                $this->messageManager->addErrorMessage(
                    __('The nickname must match the name associated with this order.')
                );
                return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
            }
        }

        // 7. Check for duplicate review (anti-spam)
        if ($orderIncrementId) {
            if ($this->isAlreadyReviewed($orderIncrementId, $productId)) {
                $this->messageManager->addErrorMessage(
                    __('You have already submitted a review for this product from this order.')
                );
                return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
            }
        }

        // 8. Save the review
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $customerId = $this->customerSession->getCustomerId() ?: null;

            $review = $this->reviewFactory->create();
            $review->setData([
                'title' => $title,
                'detail' => $detail,
                'nickname' => $nickname,
                'status_id' => Review::STATUS_PENDING,
                'entity_id' => $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE),
                'entity_pk_value' => $productId,
                'store_id' => $storeId,
                'stores' => [$storeId],
                'customer_id' => $customerId,
            ]);

            $review->save();
            $review->aggregate();

            // 9. Save rating votes
            if (!empty($ratings)) {
                foreach ($ratings as $ratingId => $optionId) {
                    $this->ratingFactory->create()
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId($customerId)
                        ->addOptionVote($optionId, $productId);
                }
                $review->aggregate();
            }

            // 10. Track the review to prevent duplicates
            if ($orderIncrementId) {
                $this->saveTrackingRecord($orderIncrementId, $productId);
            }

            // 11. Send Thank You email with coupon code if enabled
            if ($this->helper->isThankYouEmailEnabled()) {
                $customerEmail = '';
                $customerName = $nickname;
                if ($this->customerSession->isLoggedIn()) {
                    $customerEmail = $this->customerSession->getCustomer()->getEmail();
                    $customerName = $this->customerSession->getCustomer()->getFirstname();
                }

                if (!empty($customerEmail)) {
                    try {
                        $productModel = $this->productRepository->getById($productId);
                        $this->helper->sendThankYouEmail([
                            'customer_name'  => $customerName,
                            'customer_email' => $customerEmail,
                            'product_name'   => $productModel->getName(),
                        ]);
                    } catch (\Exception $e) {
                        // Silently fail - the review was already saved successfully
                    }
                }
            }

            $this->messageManager->addSuccessMessage(
                __('Your review has been submitted successfully and is pending approval. Thank you!')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while submitting your review. Please try again.')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/add', $redirectParams);
    }

    /**
     * Check if a review has already been submitted for this product+order combination.
     *
     * @param string $orderIncrementId
     * @param int $productId
     * @return bool
     */
    private function isAlreadyReviewed($orderIncrementId, $productId)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('meetanshi_reviewreminder_tracking');
            $select = $connection->select()
                ->from($tableName, ['entity_id'])
                ->where('order_increment_id = ?', $orderIncrementId)
                ->where('product_id = ?', $productId);
            return (bool) $connection->fetchOne($select);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save tracking record after successful review submission.
     *
     * @param string $orderIncrementId
     * @param int $productId
     * @return void
     */
    private function saveTrackingRecord($orderIncrementId, $productId)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('meetanshi_reviewreminder_tracking');
            $customerEmail = null;
            if ($this->customerSession->isLoggedIn()) {
                $customerEmail = $this->customerSession->getCustomer()->getEmail();
            }
            $connection->insertOnDuplicate(
                $tableName,
                [
                    'order_increment_id' => $orderIncrementId,
                    'product_id' => $productId,
                    'customer_email' => $customerEmail,
                ],
                ['entity_id']
            );
        } catch (\Exception $e) {
            // Silently fail on tracking - the review was already saved successfully
        }
    }
}
