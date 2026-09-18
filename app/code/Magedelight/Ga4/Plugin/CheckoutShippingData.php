<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface as quote;
use Magedelight\Ga4\Helper\Data as mdhelper;
use Magento\Checkout\Model\ShippingInformationManagement as ShippingInfo;
use Magedelight\Ga4\Model\EventTrigger;

class CheckoutShippingData
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quote;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $mdhelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param CheckoutSession $session
     * @param quote $quote
     * @param mdhelper $mdhelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(CheckoutSession $session, quote $quote, mdhelper $mdhelper, EventTrigger $eventTrigger)
    {
        $this->session = $session;
        $this->quote = $quote;
        $this->mdhelper = $mdhelper;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * AroundSaveAddressInformation
     *
     * @param ShippingInfo $subject
     * @param Closure $proceed
     * @param cartId $cartId
     * @param addressInformation $addressInformation
     */
    public function aroundSaveAddressInformation(
        ShippingInfo $subject,
        \Closure $proceed,
        $cartId,
        $addressInformation
    ) {
        $data = $proceed($cartId, $addressInformation);
        if ($this->mdhelper->isGTMStatus() && $this->mdhelper->GTMEventConfigured('add_shipping_info')) {
            $cartData = $this->quote->getActive($cartId);
            $getShippingInfo = $cartData->getShippingAddress()->getShippingDescription();
            $getShippingInfoPrice = $cartData->getShippingAddress()->getShippingAmount();
            $eventName = "add_shipping_info";
            $this->session->setCheckoutOptionsData(
                $this->eventTrigger->shippingCheckoutEventTrigger(
                    '2',
                    $getShippingInfo,
                    $getShippingInfoPrice,
                    $eventName
                )
            );
        }
        return $data;
    }
}
