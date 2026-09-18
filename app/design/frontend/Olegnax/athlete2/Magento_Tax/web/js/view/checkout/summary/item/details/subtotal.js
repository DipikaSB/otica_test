/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Checkout/js/view/summary/item/details/subtotal'
], function (subtotal) {
    'use strict';

    var displayPriceMode = window.checkoutConfig.reviewItemPriceDisplayMode || 'including';

    return subtotal.extend({
        defaults: {
            displayPriceMode: displayPriceMode,
            template: 'Magento_Tax/checkout/summary/item/details/subtotal'
        },

        /**
         * @return {Boolean}
         */
        isPriceInclTaxDisplayed: function () {
            return displayPriceMode == 'both' || displayPriceMode == 'including'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isPriceExclTaxDisplayed: function () {
            return displayPriceMode == 'both' || displayPriceMode == 'excluding'; //eslint-disable-line eqeqeq
        },

        /**
         * @param {Object} quoteItem
         * @return {*|String}
         */
        getValueInclTax: function (quoteItem) {
            return this.getFormattedPrice(quoteItem['row_total_incl_tax']);
        },

        /**
         * @param {Object} quoteItem
         * @return {*|String}
         */
        getValueExclTax: function (quoteItem) {
            return this.getFormattedPrice(quoteItem['row_total']);
        },
        getOfferMeter: function (quoteItem) {
            var discount = quoteItem.extension_attributes.product_discount_price;
            if (!discount || discount <= 0) {
                return null; // Let KO handle visibility
            }
            return discount + '%' + ' OFF';
        },

        getFinalPrice: function (quoteItem) {
            var finalPrice = quoteItem.extension_attributes.product_final_price;
            if (!finalPrice || finalPrice <= 0) {
                return null; // Let KO handle visibility
            }
            return this.getFormattedPrice(finalPrice);
        }

    });
});
