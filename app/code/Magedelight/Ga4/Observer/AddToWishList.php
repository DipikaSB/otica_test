<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Ga4\Helper\Data;
use Magento\Customer\Model\Session;
use Magedelight\Ga4\Model\EventTrigger;

class AddToWishList implements ObserverInterface
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $datahelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param Session $customerSession
     * @param Data $datahelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(Session $customerSession, Data $datahelper, EventTrigger $eventTrigger)
    {
        $this->customerSession = $customerSession;
        $this->datahelper = $datahelper;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->datahelper->isGTMStatus() && $this->datahelper->GTMEventConfigured('add_to_wishlist')) {
            $product = $observer->getEvent()->getProduct();
            $event = "add_to_wishlist";
            $this->customerSession->setAddToWishListData($this->eventTrigger->itemAddedEventTrigger($event, $product));
        }

        return $this;
    }
}
