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

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
		increaseItem : function(item_id) {
			var newVal;
			var oldValue = parseFloat($('.qty-item-group input#item_' + item_id).val());
			newVal = oldValue + 1;
			$('.qty-item-group input#item_' + item_id).val(newVal);
			return self.ajaxUpdateItem('/infortis/index/update',item_id ,newVal);
        },
		decreaseItem : function(item_id) {
			var newVal, candidateNewValue;
			var oldValue = parseFloat($('.qty-item-group input#item_' + item_id).val());
			newVal = oldValue - 1;
			if (newVal > 0){
				$('.qty-item-group input#item_' + item_id).val(newVal);
				return self.ajaxUpdateItem('/infortis/index/update',item_id ,newVal);
			}else{
				return self.deleteItem(item_id);
			}
        },
		deleteItem : function(item_id) {
			var rqUrl = '/infortis/index/delete';
			console.log('delete item ' + item_id);
			fullScreenLoader.startLoader();
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: item_id},
				success: function(response){
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
		ajaxUpdateItem: function(rqUrl, itemId, itemQty){
			/* setTimeout(function(){ alert("Hello"); }, 3000); */
            fullScreenLoader.startLoader();
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: itemId, qty: itemQty},
				success: function(response){
					var deferred = $.Deferred();
					self.updateamount();
					
					var sections = ['cart'];
					customerData.invalidate(sections);
					customerData.reload(sections, true);
					
					fullScreenLoader.stopLoader();
					
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
