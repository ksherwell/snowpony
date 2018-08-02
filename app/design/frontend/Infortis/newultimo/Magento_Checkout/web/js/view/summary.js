/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/totals',
	'Magento_Checkout/js/model/step-navigator'
], function ($, Component, totals, stepNavigator) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,
		isVisible: function () {
                return stepNavigator.isProcessed('shipping');
		},
		initialize: function () {
			$(function() {
				$('body').on("click", '#place-order-trigger', function () {
					$(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
				});
			});
			var self = this;
			this._super();
		}
    });
});
