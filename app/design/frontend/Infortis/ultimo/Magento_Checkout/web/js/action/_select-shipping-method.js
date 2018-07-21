/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
	'jquery',
    '../model/quote',
	'Magento_Checkout/js/action/set-shipping-information'
], function ($,quote,setShippingAction) {
    'use strict';

    return function (shippingMethod) {
        quote.shippingMethod(shippingMethod);
		//console.log(shippingMethod.method_code);
		if (shippingMethod != null){
			setShippingAction([]);
		}
			
		//$('#co-shipping-method-form').find('.action.continue').trigger( 'click' );
    };
});
