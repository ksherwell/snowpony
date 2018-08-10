 define([
	'jquery',
	'jquery/ui',
    'uiRegistry',
	'uiComponent',
	'Magento_Checkout/js/model/totals',
	'Magento_Customer/js/customer-data',
	'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Checkout/js/model/cart/totals-processor/default'
	], function ($, ui , registry, Component, totals, customerData, getTotalsAction, cartCache, defaultTotal) {
	"use strict";

	var self;

	return Component.extend({
		initialize: function () {
			self = this;
			this._super();

            $('#extrasbox .caddie-extra-cart button.increase').click(function (e) {
                var entity_id= $(this).parents('li').attr('data-id');
                self.increaseExtraItem(entity_id);
            });

			$('#extrasbox .caddie-extra-cart button.decrease').click(function (e) {
				var entity_id = $(this).parents('li').attr('data-id');
				self.decreaseExtraItem(entity_id);
			});
		},

		increaseExtraItem: function(entity_id) {
			var newVal;
			var oldValue = parseFloat($('.caddie-extra-cart input#item_' + entity_id).val());
			newVal = oldValue + 1;
			$('.caddie-extra-cart input#item_' + entity_id).val(newVal);

			self.ajaxUpdateItem('/infortis/extras/update', entity_id ,newVal);
        },

		decreaseExtraItem: function(entity_id) {
			var newVal;
			var oldValue = parseFloat($('.caddie-extra-cart input#item_' +  entity_id).val());
			if(oldValue > 0 ) {
                newVal = oldValue - 1;
            } else {
				newVal = 0;
			}
			$('.caddie-extra-cart input#item_' +  entity_id).val(newVal);

			if (newVal > 0){
				self.ajaxUpdateItem('/infortis/extras/update', entity_id, newVal);
			}else{
				self.deleteExtraItem(entity_id, newVal);
			}
        },

		deleteExtraItem: function(entity_id, itemQty) {
			var rqUrl = '/infortis/extras/delete';
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: entity_id},
				success: function(response){
                    var item_id = -1;
                    for (var i = 0; i < response.extras.length; i++) {
                        if(response.extras[i].entity_id == entity_id){
                            item_id = response.extras[i].itemId;
                        }
                    }
					self.updateamount(item_id, itemQty);
				}
			});
        },

		ajaxUpdateItem: function (rqUrl, entity_id, itemQty) {
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: entity_id, qty: itemQty},
				success: function(response){
					var item_id = -1;
					if(response.extras){
                    	for (var i = 0; i < response.extras.length; i++) {
                    		if(response.extras[i].entity_id == entity_id){
                    		item_id = response.extras[i].itemId;
							}
                    	}
					}

					self.updateamount(item_id, itemQty);

					if (!response.status) {
						var bkQty = $('.caddie-extra-cart input#item_' + entity_id).val() - 1;
						$('.caddie-extra-cart input#item_' + entity_id).val(bkQty);
						alert(response.message);
					}
				}
			});
        },

		updateamount :function(itemId, itemQty){
			var form = $('.form-cart#form-validate');
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				showLoader: true,

				success: function (res) {
					var parsedResponse = $.parseHTML(res);
					var result = $(parsedResponse).find("#form-validate");

					$("#form-validate").replaceWith(result);

                    if(itemQty > 0){
                        $('input#cart-' + itemId + '-qty.input-text.qty').val(itemQty);
                    }

                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);

                    //after successfull execution you need to add these lines.
                    cartCache.set('totals',null);
                    defaultTotal.estimateTotals();
				},
				error: function(xhr, status, error) {
					var err = eval("(" + xhr.responseText + ")");
					console.log(err.Message);
				}
			});
		}
	});
});
