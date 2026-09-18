// KnockoutJS logic - this is the file which gets rendered on the customer view in the frontend
define([
    "Magento_Checkout/js/view/payment/default",
    "Magento_Checkout/js/model/quote",
    "jquery",
    "ko",
    "Magento_Checkout/js/model/payment/additional-validators",
    "Magento_Checkout/js/action/set-payment-information",
    "mage/url",
    "Magento_Customer/js/model/customer",
    "Magento_Checkout/js/action/place-order",
    "Magento_Checkout/js/model/full-screen-loader",
    "Magento_Ui/js/model/messageList",
    "Magento_Checkout/js/model/shipping-save-processor",
    "Magento_Checkout/js/action/select-payment-method",
    "Magento_Checkout/js/checkout-data",
], function (
    Component,
    quote,
    $,
    ko,
    additionalValidators,
    setPaymentInformationAction,
    urlBuilder,
    customer,
    placeOrderAction,
    fullScreenLoader,
    messageList,
    shippingSaveProcessor,
    selectPaymentMethodAction,
    checkoutData
) {
    "use strict";

    return Component.extend({
        defaults: {
            template: "Phonepe_PG/payment/phonepe",
        },

        initialize: function () {
            this._super();
            return this;
        },

        getCode: function () {
            return "phonepe_pg";
        },

        getTitle: function () {
            return "PhonePe Payment Gateway";
        },

        getLogoUrl: function () {
            return window.require.toUrl("Phonepe_PG/assets/phonepe_pg.png");
        },

        isRadioButtonVisible: function () {
            return true;
        },

        getPaymentConfig: function () {
            return window.checkoutConfig.payment[this.getCode()];
        },

        loadPhonePeScript: function () {
            var self = this;
            return new Promise(function (resolve, reject) {
                if (window.PhonePeCheckout) {
                    resolve();
                    return;
                }
                var scriptUrl = self.getPaymentConfig().checkoutJsUrl;
                if (!scriptUrl) {
                    console.error("PhonePe checkoutJsUrl not found in config.");
                    reject();
                    return;
                }

                var script = document.createElement("script");
                script.src = scriptUrl;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },

        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                fullScreenLoader.startLoader();

                this.loadPhonePeScript()
                    .then(function () {
                        self.getPlaceOrderDeferredObject()
                            .fail(function () {
                                fullScreenLoader.stopLoader();
                                self.isPlaceOrderActionAllowed(true);
                            })
                            .done(function () {
                                $.ajax({
                                    url: urlBuilder.build(
                                        "phonepe/index/redirect"
                                    ),
                                    type: "POST",
                                    dataType: "json",
                                    success: function (response) {
                                        if (
                                            response.success &&
                                            response.redirectUrl
                                        ) {
                                            const sdkCallback = (
                                                sdkResponse
                                            ) => {
                                                fullScreenLoader.startLoader();
                                                window.location.replace(
                                                    urlBuilder.build(
                                                        "phonepe/index/callback"
                                                    )
                                                );
                                            };

                                            PhonePeCheckout.transact({
                                                tokenUrl: response.redirectUrl,
                                                callback: sdkCallback,
                                                type: self
                                                    .getPaymentConfig()
                                                    .displayMode.toUpperCase(),
                                            });
                                        } else {
                                            fullScreenLoader.stopLoader();
                                            messageList.addErrorMessage({
                                                message:
                                                    response.message ||
                                                    "An error occurred while intiating your payment. Redirecting back to cart.",
                                            });
                                            self.isPlaceOrderActionAllowed(
                                                true
                                            );
                                            setTimeout(function () {
                                                window.location.replace(
                                                    urlBuilder.build(
                                                        "checkout/cart"
                                                    )
                                                );
                                            }, 1000);
                                        }
                                    },
                                });
                            });
                    })
                    .catch(function () {
                        fullScreenLoader.stopLoader();
                        messageList.addErrorMessage({
                            message:
                                response.message ||
                                "An unexpected error occurred while processing your payment. Please try again later.",
                        });
                        self.isPlaceOrderActionAllowed(true);
                    });

                return true;
            }

            return false;
        },
        getPlaceOrderDeferredObject: function () {
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },
    });
});
