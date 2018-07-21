 define([
	'jquery', 
	'jquery/ui',
    'uiRegistry',
	'uiComponent', 
	'Magento_Checkout/js/model/totals',
	'Magento_Customer/js/customer-data',
	'Magento_Checkout/js/action/get-totals'
	], function ($, ui , registry, Component, totals, customerData,getTotalsAction) {
	"use strict";
	var self;
	return Component.extend({
		initialize: function () {
			self = this;
			this._super();
			
			$('#extrasbox .caddie-extra-cart button.decrease').click(function (e) { 
				var item_id = $(this).parents('li').attr('data-id');
				self.decreaseExtraItem(item_id);
			});
			
			$('#extrasbox .caddie-extra-cart button.increase').click(function (e) { 
				var item_id = $(this).parents('li').attr('data-id');
				self.increaseExtraItem(item_id);
			});
			
		},
		increaseExtraItem: function (item_id) {
			var newVal;
			var oldValue = parseFloat($('.caddie-extra-cart input#item_' + item_id).val());
			newVal = oldValue + 1;
			$('.caddie-extra-cart input#item_' + item_id).val(newVal);
			self.ajaxUpdateItem('/infortis/extras/update',item_id ,newVal);
        },
		decreaseExtraItem: function (item_id) {
			var newVal, candidateNewValue;
			var oldValue = parseFloat($('.caddie-extra-cart input#item_' + item_id).val());
			newVal = oldValue - 1;
			$('.caddie-extra-cart input#item_' + item_id).val(newVal);
			if (newVal > 0){
				self.ajaxUpdateItem('/infortis/extras/update',item_id ,newVal);
			}else{
				self.deleteExtraItem(item_id);
			}
        },
		deleteExtraItem: function (item_id) {
			var rqUrl = '/infortis/extras/delete';
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: item_id},
				success: function(response){
					/* var deferred = $.Deferred(); */
					self.updateamount();
					
					/* var sections = ['cart']; */
					/* customerData.invalidate(sections);
					customerData.reload(sections, true); */
				}
			});
        },
		ajaxUpdateItem: function (rqUrl, itemId, itemQty) {
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: {itemid: itemId, qty: itemQty},
				success: function(response){
					
					self.updateamount();
					
					if (!response.status) {
						var bkQty = $('.caddie-extra-cart input#item_' + itemId).val() - 1;
						$('.caddie-extra-cart input#item_' + itemId).val(bkQty);
						alert(response.message);
					}
					
					/* var deferred = $.Deferred();
					self.updateamount();
					var sections = ['cart']; */
					/* customerData.invalidate(sections);
					customerData.reload(sections, true); */
					
				}
			});
        },
		updateamount :function(){
			var form = $('.form-cart#form-validate');
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				showLoader: true,
				success: function (res) {
					var parsedResponse = $.parseHTML(res);
					var result = $(parsedResponse).find("#form-validate");
					var sections = ['cart'];

					$("#form-validate").replaceWith(result);

					// The mini cart reloading
					customerData.reload(sections, true);

					// The totals summary block reloading
					var deferred = $.Deferred();
					getTotalsAction([], deferred);
					
					$.when(deferred).done(function() {
						totals.isLoading(false);
					});
				},
				error: function (xhr, status, error) {
					var err = eval("(" + xhr.responseText + ")");
					console.log(err.Message);
				}
			});
		}
	});
});