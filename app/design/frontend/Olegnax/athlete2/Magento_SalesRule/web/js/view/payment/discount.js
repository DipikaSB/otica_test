/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Ui/js/modal/confirm'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon, confirm) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() !== null && couponCode() !== '');

    // Handle promo-code and remove events globally
    $(document).on("click", ".promo-code", function () {
        var codeValue = $(this).attr("val");

        if (codeValue) {
            couponCode(codeValue);
            setCouponCodeAction(codeValue, isApplied);
        }
    });

    $(document).on("click", ".sp-coupon-remove", function () {
        couponCode('');
        cancelCouponAction(isApplied);
    });


    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        couponCode: couponCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            let form = '#discount-form';

            $(form + ' input[type="text"]').each(function () {
                let currentValue = $(this).val();

                $(this).val(currentValue.trim());
            });
            return $(form).validation() && $(form).validation('isValid');
        },

        getCmsBlockSummary: function () {
            return $('.coupon_summary_block').html();
        }
    });
});
