<?php

namespace Meetanshi\ReviewReminderBasic\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Meetanshi\ReviewReminderBasic\Helper\Data;
use Psr\Log\LoggerInterface;

class Reminder
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SortOrderBuilder
     */
    private $sortBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Reminder constructor.
     *
     * @param Data $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortBuilder,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
        $this->sortBuilder = $sortBuilder;
        $this->logger = $logger;
    }

    /**
     * Execute cron job.
     *
     * @return $this|string
     */
    public function execute()
    {
        if ($this->helper->getConfig()):
            try {
                $day = '-' . $this->helper->getDays() . ' day';
                $to = $this->helper->getCurrentTime();
                $from = strtotime($day, strtotime($to));
                $from = date('Y-m-d h:i:s', $from);

                $currentDate = date('Y-m-d', strtotime($day, strtotime($to)));
                $currentDateFrom = $currentDate.' 00:00:00';
                $currentDateTo = $currentDate.' 23:59:59';

                $allowedStatuses = $this->helper->getOrderStatuses();

                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter('status', implode(',', $allowedStatuses), 'in')
                    ->addFilter('created_at', $currentDateFrom, 'gteq')
                    ->addFilter('created_at', $currentDateTo, 'lteq')
                    ->addSortOrder($this->sortBuilder->setField('entity_id')
                        ->setDescendingDirection()->create())
                    ->setPageSize(100)->setCurrentPage(1)->create();

                $ordersList = $this->orderRepository->getList($searchCriteria);

                foreach ($ordersList as $order):
                    $config = [];
                    $config['incrementId'] = $order->getIncrementId();
                    $config['mail'] = $order->getCustomerEmail();
                    $config['customer'] = $order->getBillingAddress()->getFirstName();
                    $config['customer_name'] = $order->getBillingAddress()->getFirstName();
                    $config['date'] = date('M d, Y h:i:s A', strtotime($order->getcreatedAt()));
                    $config['storeId'] = $order->getStoreId();
                    $product = [];
                    foreach ($order->getAllVisibleItems() as $item):
                        $product[] = $item->getProductId();
                    endforeach;
                    $config['reminder']['product_id'] = implode(',', $product);
                    $config['reminder']['increment_id'] = $order->getIncrementId();
                    $config['reminder']['customer_name'] = $order->getBillingAddress()->getFirstName();
                    $this->helper->sendReviewReminderMail($config);
                endforeach;

            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
                return $e->getMessage();
            }
        endif;
        return $this;
    }
}
