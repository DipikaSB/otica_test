<?php
namespace Phonepe\PG\Model\Constants;

/**
 * Class MessageConstants holds all the user-facing and log messages for the module.
 */
class MessageConstants
{
    /**
     * User-facing error messages
     * These are safe to show to the customer.
     */
    const ERROR_GENERIC_PAYMENT_FAILURE = 'An unexpected error occurred while processing your payment. Please try again later.';
    const ERROR_NO_ORDER_IN_SESSION     = 'No valid order found in session. Please try again.';
    const ERROR_REDIRECT_URL_MISSING    = 'Could not initiate payment. Please try again or contact support.';
}
