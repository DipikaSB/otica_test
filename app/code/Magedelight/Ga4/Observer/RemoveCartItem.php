<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Observer;

use Magedelight\Ga4\Helper\Data as Datahelper;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as checkoutSession;
use Magedelight\Ga4\Model\EventTrigger;

class RemoveCartItem implements \Magento\Framework\Event\ObserverInterface
{
   /**
    * @var \Magento\Catalog\Model\ProductRepository
    */
    protected $catalogRepo;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $datahelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param ProductRepository $catalogRepo
     * @param Datahelper $datahelper
     * @param checkoutSession $checkoutSession
     * @param EventTrigger $eventTrigger
     */
    public function __construct(
        ProductRepository $catalogRepo,
        Datahelper $datahelper,
        checkoutSession $checkoutSession,
        EventTrigger $eventTrigger
    ) {
        $this->catalogRepo = $catalogRepo;
        $this->datahelper = $datahelper;
        $this->checkoutSession = $checkoutSession;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * Remove Cart Item.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return RemoveCartItem
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
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
            $this->datahelper->GTMEventConfigured('remove_from_cart')) {
            $cartData = $observer->getData('quote_item');
            $pid = $cartData->getData('product_id');

            if (!$pid) {
                return $this;
            }
            $variant = "";

            if ($this->datahelper->isVariantEnabled()) {
                $option = $cartData->getProduct()->getTypeInstance(true)->getOrderOptions($cartData->getProduct());
                $ptype = $cartData->getProductType();
                $variant = $this->datahelper->getProductOption($option, $ptype);
            }

            $product = $this->catalogRepo->getById($pid);
            $qty = $cartData->getData('qty');
            $item_price = $this->datahelper->getItemPrice($cartData);
            $eventName = 'remove_from_cart';
            $this->checkoutSession
            ->setRemoveFromCartData($this->eventTrigger
                ->cartEventTrigger(
                    $eventName,
                    $qty,
                    $product,
                    $item_price,
                    $variant
                ));
        }
        return $this;
    }
}
