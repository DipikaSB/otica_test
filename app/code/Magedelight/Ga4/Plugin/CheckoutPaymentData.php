<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface as Salesorder;
use Magedelight\Ga4\Helper\Data as mdhelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\PaymentInformationManagement as PaymentInfo;
use Magedelight\Ga4\Model\EventTrigger;

class CheckoutPaymentData
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $salesorder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

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
     * @param Salesorder $salesorder
     * @param mdhelper $mdhelper
     * @param CheckoutSession $checkoutSession
     * @param EventTrigger $eventTrigger
     */
    public function __construct(
        Salesorder $salesorder,
        mdhelper $mdhelper,
        CheckoutSession $checkoutSession,
        EventTrigger $eventTrigger
    ) {
        $this->mdhelper = $mdhelper;
        $this->checkoutSession = $checkoutSession;
        $this->salesorder = $salesorder;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * AfterSavePaymentInformationAndPlaceOrder
     *
     * @param PaymentInfo $subject
     * @param Result $result
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInfo $subject,
        $result
    ) {
        if ($this->mdhelper->isGTMStatus() && $this->mdhelper->GTMEventConfigured('add_payment_info')) {
            $salesData = $this->checkoutSession->getLastRealOrder();
            if (!$salesData->getId()) {
                try {
                    $id = $result;
                    $salesData = $this->salesorder->get($id);
                } catch (\Exception $ex) {
                    return $result;
                }
            }

            $getCheckoutData = $salesData->getPayment()->getAdditionalInformation();
            if ($getCheckoutData && isset($getCheckoutData['method_title'])) {
                $methodName = $getCheckoutData['method_title'];
                $eventName = "add_payment_info";
                $this->checkoutSession->setCheckoutOptionsData(
                    $this->eventTrigger->paymentCheckoutEventTrigger(
                        '3',
                        $methodName,
                        $eventName
                    )
                );
            }
        }
        return $result;
    }
}
