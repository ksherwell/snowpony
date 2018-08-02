/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery',
	'ko',
	'uiComponent',
	'underscore',
	'mage/validation'
], function ($, ko, Component, _) {
    'use strict';

    return Component.extend({
        defaults: {
            displayArea: 'after_details',
            template: 'Magento_GiftMessage/custom-gift-message-item-level'
        },
		toggleVisibility: function (itemId) { 
			$('#gift-content-'+ itemId).toggle();
		},
        initialize: function () {
            var self = this,
                model;
            this._super();
        },
		getItemGift: function(itemId){
			var message = false;
			message = window.giftOptionsConfig.giftMessage.hasOwnProperty('itemLevel') &&
                        window.giftOptionsConfig.giftMessage.itemLevel.hasOwnProperty(itemId) ?
                            window.giftOptionsConfig.giftMessage.itemLevel[itemId].message :
                            null;
			return 	message;	
		},
		messageCount: function(itemId){
			return ko.computed(function () {
				var messLength = this.getMessage(itemId) != null ? this.getMessage(itemId).length : 0;
				var countNum = 500 - messLength;
				return countNum;
			}, this);
		},
		getRecipient: function(itemId){
			if (_.isObject(this.getItemGift(itemId))) {
				return this.getItemGift(itemId).recipient;
			}
			return null;
		},
		getsender: function(itemId){
			if (_.isObject(this.getItemGift(itemId))) {
				return this.getItemGift(itemId).sender;
			}
			return null;
		},
		getMessage: function(itemId){
			if (_.isObject(this.getItemGift(itemId))) {
				return this.getItemGift(itemId).message;
			}
			return null;
		},
		getIsAvailable: function(itemId){
			var is_available = false;
			is_available = window.giftOptionsConfig.giftMessage.hasOwnProperty('itemLevel') &&
                        window.giftOptionsConfig.giftMessage.itemLevel.hasOwnProperty(itemId) ?
                            window.giftOptionsConfig.giftMessage.itemLevel[itemId].is_available : null;
							
			return is_available;
		},
		validateForm: function (form) {
			return $(form).validation() && $(form).validation('isValid');
		},     
        deleteOptions: function (itemId) {
			var formId =  '#gift-options-form-'+ itemId;
			if (!($(formId).validation() && $(formId).validation('isValid'))) {
			   return;
			}
            var rqData = $(formId).serialize();
            rqData = $(formId).serialize() + '&itemId=' + itemId;
			var rqUrl = '/infortis/giftmessage/delete';
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: rqData,
				success: function(response){
					if(response.status){
						$('#gift-options-item-'+ itemId).find('.gift-message-summary .message-content').html('');
						$('#gift-options-item-'+ itemId).find('button.action-delete').hide();
						$('#gift-options-item-'+ itemId).find('input,textarea').val('');
						$('#gift-content-'+ itemId).hide();
					}
				}
			});
        },
        submitOptions: function (itemId) {
			var formId =  '#gift-options-form-'+ itemId;
			if (!($(formId).validation() && $(formId).validation('isValid'))) {
			   return;
			}
            var rqData = $(formId).serialize();
            rqData = $(formId).serialize() + '&itemId=' + itemId;
			var rqUrl = '/infortis/giftmessage/update';
			$.ajax({
				url: rqUrl,
				type: "POST",
				data: rqData,
				success: function(response){
					if(response.status){
						var htmlResponse = '<span>Message :</span>' + response.gift.message;
						$('#gift-options-item-'+ itemId).find('.gift-message-summary .message-content').html(htmlResponse);
						$('#gift-options-item-'+ itemId).find('button.action-delete').show();
						$('#gift-content-'+ itemId).hide();
					}
				}
			});
        },
		hideBlock: function (itemId) { 
           $('#gift-content-'+ itemId).hide();
        }
    });
});
