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
            template: 'Magento_Checkout/extras/item/extras-details'
        },
		initialize: function () {
			self = this;
			this._super();
		},
		getProductId: function (quoteItem) {
            return quoteItem.product_id;
        },
        getValue: function (quoteItem) {
            return quoteItem.qtyQuote;
        },
		getSrc : function(quoteItem) {
			return quoteItem.thumbnail;
        },
		getAlt : function(quoteItem) {
			return quoteItem.name;
        },
		increaseExtraItem : function(item_id) {
			var newVal;
			var oldValue = parseFloat($('.caddie-extra-cart input#item_' + item_id).val());
			newVal = oldValue + 1;
			$('.caddie-extra-cart input#item_' + item_id).val(newVal);
			return self.ajaxUpdateItem('/infortis/extras/update',item_id ,newVal);
        },
		decreaseExtraItem : function(item_id) {
			var newVal, candidateNewValue;
			var oldValue = parseFloat($('.caddie-extra-cart input#item_' + item_id).val());
            if(oldValue > 0 ) {
                newVal = oldValue - 1;
            } else {
                newVal = 0;
            }
			$('.caddie-extra-cart input#item_' + item_id).val(newVal);
			if (newVal > 0){
				return self.ajaxUpdateItem('/infortis/extras/update',item_id ,newVal);
			}else{
				return self.deleteExtraItem(item_id);
			}
        },
		deleteExtraItem : function(item_id) {
			var rqUrl = '/infortis/extras/delete';
			fullScreenLoader.startLoader();
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: item_id},
				success: function(response){
					/* if (response.redirectUrl) {
						window.location.href = response.redirectUrl;
					}else{ */
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
					//}
				}
			});
        },
		ajaxUpdateItem: function(rqUrl, itemId, itemQty){
            fullScreenLoader.startLoader();
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: itemId, qty: itemQty},
				success: function(response){
					if(response.imageData){
						window.checkoutConfig.imageData = response.imageData;
					}
					var deferred = $.Deferred();
					self.updateamount();
					
					var sections = ['cart'];
					customerData.invalidate(sections);
					customerData.reload(sections, true);
					$('.minicart-items').trigger('change');

					if (!response.status) {
						var bkQty = $('.caddie-extra-cart input#item_' + itemId).val() - 1;
						$('.caddie-extra-cart input#item_' + itemId).val(bkQty);
						alert(response.message);
					}
					
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
