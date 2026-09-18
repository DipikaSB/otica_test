<?php
/**
 * PhonePe Payment Status Constants
 */
namespace Phonepe\PG\Model\Constants;

use Magento\Sales\Model\Order;

class StatusConstants
{
    const COMPLETED               = 'COMPLETED';
    const FAILED                  = 'FAILED';
    const PENDING                 = 'PENDING';
    const CANCELLED               = 'CANCELLED';
    const EXPIRED                 = 'EXPIRED';
    const WEBHOOK_PAYMENT_SUCCESS = 'checkout.order.completed';
    const WEBHOOK_PAYMENT_FAILURE = 'checkout.order.failed';

    const FINAL_STATUSES = [
        self::COMPLETED,
        self::FAILED,
        self::CANCELLED,
        self::EXPIRED,
    ];

    const MAGENTO_FINAL_STATES = [
        Order::STATE_CANCELED,
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
        Order::STATE_CLOSED,
        Order::STATE_HOLDED,
    ];
}
