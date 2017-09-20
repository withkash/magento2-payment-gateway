/*browser:true*/
/*global define*/
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
                type: 'kash_gateway',
                component: 'Kash_Gateway/js/view/payment/method-renderer/kash_gateway'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
