<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magedelight\Ga4\Helper\Data;
use Magento\Checkout\Model\Session;
use Magedelight\Ga4\Model\EventTrigger;

class AddtoCartAfter implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $datahelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param Session $checkoutSession
     * @param Data $datahelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(
        Session $checkoutSession,
        Data $datahelper,
        EventTrigger $eventTrigger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->datahelper = $datahelper;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return AddtoCartAfter
     */
    public function execute(Observer $observer)
    {
        $cookieConcent = $this->datahelper->getCookie();
        $accessible = 1;
        if ($this->datahelper->getGDPRStatus()) {
            if (isset($cookieConcent)) {
                $accessible = 1;
            } else {
                $accessible = 0;
            }
        }
        if ($this->datahelper->isGTMStatus() &&
            $accessible == 1 &&
            $this->datahelper->GTMEventConfigured('add_to_cart')) {
            $product = $observer->getEvent()->getProduct();
            $item = $observer->getEvent()->getData('quote_item');
            $item_price = $this->datahelper->getItemPrice($item);
            if ($product->getTypeId()=='grouped') {
                $qty = $item->getQtyToAdd();
            } else {
                $qty = $product->getQty();
            }
            $event = "add_to_cart";
            $variant = "";

            if ($this->datahelper->isVariantEnabled()) {
                $option = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $ptype = $item->getProductType();
                $variant = $this->datahelper->getProductOption($option, $ptype);
            }

            $this->checkoutSession
            ->setAddToCartData(
                $this->eventTrigger
                ->cartEventTrigger(
                    $event,
                    $qty,
                    $product,
                    $item_price,
                    $variant
                )
            );
        }

        return $this;
    }
}
