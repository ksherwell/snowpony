/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery', 
    'uiComponent',
	'mage/storage',
	'Magento_Checkout/js/model/full-screen-loader',
	'Magento_Checkout/js/model/url-builder',
	'Magento_Checkout/js/model/error-processor',
	'Magento_Checkout/js/model/cart/totals-processor/default',
	'Magento_Checkout/js/model/cart/cache',
	'Magento_Customer/js/customer-data'
], function ($, Component , storage, fullScreenLoader, urlBuilder , errorProcessor, defaultTotal, cartCache, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/customdetails'
        },
		initialize: function () {
			self = this;
			this._super();
		},
		getSrc: function (item_id) {
            var imageData =  window.checkoutConfig.imageData;

            if (imageData[item_id]) {
                return imageData[item_id].src;
            }

            return null;
        },
        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
		increaseItem: function(item_id) {
			var newVal;
			var oldValue = parseFloat($('.qty-item-group input#item_' + item_id).val());
			newVal = oldValue + 1;
			$('.qty-item-group input#item_' + item_id).val(newVal);

            fullScreenLoader.startLoader();
            $.ajax({
            	url: '/infortis/index/update',
            	type: "POST",
            	data: {itemid: item_id, qty: newVal},
            	success: function(response){
            		if (response.extras) {
            			response.extras.forEach(function(item) {
            				$('.caddie-extra-cart input#item_' + item.entity_id).val(item.qtyQuote);
            			});
            		}
            		var deferred = $.Deferred();
            		self.updateamount();

            		var sections = ['cart'];
            		customerData.invalidate(sections);
            		customerData.reload(sections, true);

            		fullScreenLoader.stopLoader();

            	}
            });
        },
		decreaseItem: function(item_id) {
			var newVal, candidateNewValue;
			var oldValue = parseFloat($('.qty-item-group input#item_' + item_id).val());
			newVal = oldValue - 1;
			if (newVal > 0){
				$('.qty-item-group input#item_' + item_id).val(newVal);

                fullScreenLoader.startLoader();
                $.ajax({
                    url: '/infortis/index/update',
                    type: "POST",
                    data: {itemid: item_id, qty: newVal},
                    success: function(response){
                        if (response.extras) {
                            response.extras.forEach(function(item) {
                                $('.caddie-extra-cart input#item_' + item.entity_id).val(item.qtyQuote);
                            });
                        }
                        var deferred = $.Deferred();
                        self.updateamount();

                        var sections = ['cart'];
                        customerData.invalidate(sections);
                        customerData.reload(sections, true);

                        fullScreenLoader.stopLoader();

                    }
                });
			}else{
				var rqUrl = '/infortis/index/delete';
			fullScreenLoader.startLoader();
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: item_id},
				success: function(response){
					if (response.extras) {
						response.extras.forEach(function(item) {
							$('.caddie-extra-cart input#item_' + item.entity_id).val(item.qtyQuote);
						});
					}
					
					if (response.redirectUrl) {
						window.location.href = response.redirectUrl;
					}else{
						var sections = ['cart'];
						customerData.invalidate(sections);
						customerData.reload(sections, false);
						
						var deferred = $.Deferred();
						self.updateamount();
						
						fullScreenLoader.stopLoader();
					}
				}
			});
			}
        },
		deleteItem: function(item_id) {
			var rqUrl = '/infortis/index/delete';
			fullScreenLoader.startLoader();
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: item_id},
				success: function(response){
					
					if (response.extras) {
						response.extras.forEach(function(item) {
							$('.caddie-extra-cart input#item_' + item.entity_id).val(item.qtyQuote);
						});
					}
					
					if (response.redirectUrl) {
						window.location.href = response.redirectUrl;
					}else{
						var sections = ['cart'];
						customerData.invalidate(sections);
						customerData.reload(sections, false);
						
						var deferred = $.Deferred();
						self.updateamount();
						
						fullScreenLoader.stopLoader();
					}
				}
			});
        },
		updateamount:function () {
			//after successfull execution you need to add these lines.
			cartCache.set('totals',null);
			defaultTotal.estimateTotals();
		}
    });
});
