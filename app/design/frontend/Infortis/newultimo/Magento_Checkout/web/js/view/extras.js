/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'ko',
	'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/totals',
	'Magento_Checkout/js/model/step-navigator'
], function (ko, $, Component, totals, stepNavigator) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,
		initialize: function () {
			/* $(function() {
				$('body').on("click", '#place-order-trigger', function () {
					$(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
				});
			}); */
			var self = this;
			this._super();
		},
		getCrossellData: ko.observable(window.checkoutConfig.crossellData)
    });
});
