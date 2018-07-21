/**
 * Created by Linh on 6/8/2016.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'PL_Payway/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
     function ($, Component, setPaymentMethodAction, additionalValidators){
        'use strict';
       // var paymentMethod = ko.observable(null);

        return Component.extend({
            defaults: {
                template: 'PL_Payway/payment/payway-net'
            },
            getCode: function() {
                return 'payway_net';
            },

            isActive: function() {
                return true;
            },
			
            continueToPayWayNet: function () {
                if (this.validate() && additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction();
                    return false;
                }
            }
            
        });
    }
);