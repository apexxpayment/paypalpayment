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
                type: 'paypalpayment_gateway',
                component: 'Apexx_PaypalPayment/js/view/payment/method-renderer/paypalpayment_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
