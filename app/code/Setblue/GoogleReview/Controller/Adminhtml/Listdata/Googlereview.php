<?php
namespace Setblue\GoogleReview\Controller\Adminhtml\Listdata;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Setblue\GoogleReview\Model\ReviewFactory;

class Googlereview extends Action
{
    const CONFIG_PATH_PLACE_ID = 'google_review/general/place_id';
    const CONFIG_PATH_API_KEY  = 'google_review/general/api_key';

    protected $config;
    protected $resultJsonFactory;
    protected $logger;
    protected $reviewFactory;

    public function __construct(
        Action\Context $context,
        ScopeConfigInterface $config,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ReviewFactory $reviewFactory
    ) {
        $this->config = $config;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->reviewFactory = $reviewFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $placeId = $this->config->getValue(self::CONFIG_PATH_PLACE_ID);
        $apiKey  = $this->config->getValue(self::CONFIG_PATH_API_KEY);

        if (!$placeId || !$apiKey) {
            return $result->setData([
                'success' => false,
                'error' => 'API Key or Place ID missing in configuration.'
            ]);
        }

        // Get last saved review date
        $latestReview = $this->reviewFactory->create()
            ->getCollection()
            ->setOrder('review_date', 'DESC')
            ->getFirstItem();

        // If DB empty → FULL IMPORT
        $latestDate = $latestReview->getId() ? $latestReview->getReviewDate() : null;

        $nextPageToken = null;
        $saved = 0;

        try {
            do {
                $url = "https://serpapi.com/search?engine=google_maps_reviews"
                     . "&hl=en"
                     . "&place_id={$placeId}"
                     . "&api_key={$apiKey}";

                if ($nextPageToken) {
                    $url .= "&next_page_token=" . urlencode($nextPageToken);
                }

                $response = file_get_contents($url);
                $data = json_decode($response, true);

                if (isset($data['reviews'])) {
                    foreach ($data['reviews'] as $review) {

                        $googleReviewId = $review['review_id'];
                        $reviewDate = $review['iso_date'] ?? null;

                        // IF DB is NOT empty → only insert new reviews
                        if ($latestDate && $reviewDate <= $latestDate) {
                            continue;
                        }

                        // Skip duplicates
                        $existing = $this->reviewFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('review_id', $googleReviewId)
                            ->getFirstItem();

                        if ($existing->getId()) {
                            continue;
                        }

                        // Save new review
                        $model = $this->reviewFactory->create();
                        $model->setData([
                            'review_id'        => $googleReviewId,
                            'author_name'      => $review['user']['name'] ?? null,
                            'author_thumbnail' => $review['user']['thumbnail'] ?? null,
                            'review_text'      => $review['snippet'] ?? null,
                            'rating'           => $review['rating'] ?? null,
                            'review_date'      => $reviewDate,
                            'raw_json'         => json_encode($review)
                        ]);
                        $model->save();

                        $saved++;
                    }
                }

                // Pagination
                $nextPageToken = $data['serpapi_pagination']['next_page_token'] ?? null;

                if ($nextPageToken) {
                    sleep(2);
                }

            } while ($nextPageToken);

            return $result->setData([
                'success' => true,
                'message' => ($latestDate ? "Imported {$saved} new reviews." : "Imported all reviews ({$saved})."),
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Google Review Error: " . $e->getMessage());

            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}