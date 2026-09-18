define([
    "uiComponent",
    "Magento_Checkout/js/model/payment/renderer-list",
], function (Component, rendererList) {
    "use strict";

    rendererList.push({
        type: "phonepe_pg",
        component: "Phonepe_PG/js/view/payment/method-renderer/phonepe",
    });

    return Component.extend({});
});
