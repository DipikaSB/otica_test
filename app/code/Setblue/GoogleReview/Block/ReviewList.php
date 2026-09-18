<?php

namespace Setblue\GoogleReview\Block;

use Magento\Framework\View\Element\Template;
use Setblue\GoogleReview\Model\ResourceModel\Review\CollectionFactory;

class ReviewList extends Template
{
    protected $reviewCollectionFactory;

    public function __construct(
        Template\Context $context,
        CollectionFactory $reviewCollectionFactory,
        array $data = []
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Google Reviews
     */
    public function getReviews()
    {
        $collection = $this->reviewCollectionFactory->create();
        $collection->setOrder('review_date', 'DESC'); // latest first
        return $collection;
    }

    public function getRandomReviews()
    {
        $collection = $this->reviewCollectionFactory->create();

        // Exclude reviews with empty review_text
        $collection->addFieldToFilter('review_text', ['notnull' => true]);
        $collection->addFieldToFilter('review_text', ['neq' => '']); // also exclude empty strings
        $collection->addFieldToFilter('rating', ['in' => [4, 5]]);

        // Random order and limit
        $collection->getSelect()->order('RAND()');
        $collection->setPageSize(4);

        return $collection;
    }
}
