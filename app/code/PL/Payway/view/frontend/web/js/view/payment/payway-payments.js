define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'payway',
                component: 'PL_Payway/js/view/payment/method-renderer/payway-method'
            },
            {
                type: 'payway_net',
                component: 'PL_Payway/js/view/payment/method-renderer/payway-net'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);