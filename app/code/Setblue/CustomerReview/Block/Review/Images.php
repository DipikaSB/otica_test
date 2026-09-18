<?php
namespace Setblue\CustomerReview\Block\Review;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;
use Magento\Framework\Registry;

class Images extends Template
{
    protected $productRepository;
    protected $storeManager;
    protected $productFactory;
    protected $ratingFactory;
    protected $reviewFactory;
    protected $voteCollectionFactory;

    protected $registry;

    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        RatingFactory $ratingFactory,
        ReviewCollectionFactory $reviewFactory,
        VoteCollectionFactory $voteCollectionFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->ratingFactory = $ratingFactory;
        $this->reviewFactory = $reviewFactory;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getReviewsWithImages($productId)
    {
        $collection = $this->reviewFactory->create()
            ->addFieldToFilter('entity_pk_value', $productId)
            ->addFieldToFilter('status_id', 1) // approved reviews
            ->setDateOrder();

        $reviews = [];

        foreach ($collection as $review) {
            $votes = [];

            $voteCollection = $this->voteCollectionFactory->create()
                ->setReviewFilter($review->getId())
                ->addOptionInfo();

            foreach ($voteCollection as $vote) {
                $votes[] = [
                    'rating_id'   => $vote->getRatingId(),
                    'rating_code' => $vote->getRatingCode(),
                    'value'       => $vote->getValue(),       // 1–5
                    'percent'     => $vote->getPercent(),     // 20, 40…100
                ];
            }

            $reviews[] = [
                
                'review_id'   => $review->getReviewId(),
                'title'       => $review->getTitle(),
                'detail'      => $review->getDetail(),
                'nickname'    => $review->getNickname(),
                'created_at'  => $review->getCreatedAt(),
                'review_image' => $review->getData('review_image'),
                'votes'       => $votes,

            ];
        }
        return $reviews;
    }

    public function getApprovedReviewCount($productId)
    {
        $collection = $this->reviewFactory->create()
            ->addFieldToFilter('entity_pk_value', $productId)
            ->addFieldToFilter('status_id', 1); // Approved

        return $collection->getSize();
    }

    public function getCurrentProductId()
    {
        $product = $this->registry->registry('current_product');
        return $product;
    }


   
}
