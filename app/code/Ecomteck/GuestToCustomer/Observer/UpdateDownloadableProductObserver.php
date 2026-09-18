<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_GuestToCustomer
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\GuestToCustomer\Observer;

use Exception;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class UpdateDownloadableProductObserver
 * @package Ecomteck\GuestToCustomer\Observer
 */
class UpdateDownloadableProductObserver implements ObserverInterface
{
    /**
     * @var PurchasedFactory
     */
    protected $purchasedFactory;

    /**
     * @param PurchasedFactory $purchasedFactory
     */
    public function __construct(
        PurchasedFactory $purchasedFactory
    ) {
        $this->purchasedFactory = $purchasedFactory;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        $incrementId = $observer->getEvent()->getIncrementId();
        $customerId = $observer->getEvent()->getCustomerId();

        try {
            if ($incrementId && $customerId) {
                $purchased = $this->purchasedFactory->create()->load(
                    $incrementId,
                    'order_increment_id'
                );

                if ($purchased->getId()) {
                    $purchased->setCustomerId($customerId);
                    $purchased->save();
                }
            }
        } catch (Exception $e) {
            //do nothing
        }
    }
}
