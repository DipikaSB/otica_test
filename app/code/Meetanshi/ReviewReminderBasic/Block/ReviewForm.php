<?php

namespace Meetanshi\ReviewReminderBasic\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;

/**
 * Block class for the dedicated review form page.
 */
class ReviewForm extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RatingCollectionFactory
     */
    private $ratingCollectionFactory;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|false|null
     */
    private $product = null;

    /**
     * @var bool|null
     */
    private $alreadyReviewed = null;

    /**
     * ReviewForm constructor.
     *
     * @param Template\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param RatingCollectionFactory $ratingCollectionFactory
     * @param Image $imageHelper
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        RatingCollectionFactory $ratingCollectionFactory,
        Image $imageHelper,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }

    /**
     * Get product from request parameter.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|false
     */
    public function getProduct()
    {
        if ($this->product === null) {
            $productId = (int) $this->getRequest()->getParam('product_id');
            try {
                $this->product = $this->productRepository->getById($productId);
            } catch (\Exception $e) {
                $this->product = false;
            }
        }
        return $this->product;
    }

    /**
     * Get order increment ID from request parameter.
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getRequest()->getParam('order', '');
    }

    /**
     * Check if a review has already been submitted for this product+order combination.
     *
     * @return bool
     */
    public function isAlreadyReviewed()
    {
        if ($this->alreadyReviewed === null) {
            $productId = (int) $this->getRequest()->getParam('product_id');
            $orderId = $this->getRequest()->getParam('order');

            if (!$productId || !$orderId) {
                $this->alreadyReviewed = false;
                return $this->alreadyReviewed;
            }

            try {
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('meetanshi_reviewreminder_tracking');
                $select = $connection->select()
                    ->from($tableName, ['entity_id'])
                    ->where('order_increment_id = ?', $orderId)
                    ->where('product_id = ?', $productId);
                $this->alreadyReviewed = (bool) $connection->fetchOne($select);
            } catch (\Exception $e) {
                $this->alreadyReviewed = false;
            }
        }
        return $this->alreadyReviewed;
    }

    /**
     * Get active rating collection with options for star rating display.
     *
     * @return \Magento\Review\Model\ResourceModel\Rating\Collection
     */
    public function getRatings()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->ratingCollectionFactory->create()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName($storeId)
            ->setStoreFilter($storeId)
            ->setActiveFilter(true)
            ->load()
            ->addOptionToItems();
        return $collection;
    }

    /**
     * Get product image URL.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return string
     */
    public function getProductImageUrl($product)
    {
        try {
            return $this->imageHelper
                ->init($product, 'product_base_image')
                ->keepAspectRatio(true)
                ->resize(300, 300)
                ->getUrl();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get customer name from URL parameter (base64 decoded).
     *
     * @return string
     */
    public function getCustomerName()
    {
        $encodedName = $this->getRequest()->getParam('customer_name', '');
        if (!empty($encodedName)) {
            $decoded = base64_decode($encodedName, true);
            if ($decoded !== false) {
                return $decoded;
            }
        }
        return '';
    }

    /**
     * Get form action URL for review submission.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('reviewreminder/review/submit');
    }
}
