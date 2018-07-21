/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery',
	'ko',
	'uiComponent',
	'Magento_GiftMessage/js/model/gift-message',
    'Magento_GiftMessage/js/model/gift-options',
    'Magento_GiftMessage/js/action/gift-options'
], function ($, ko, Component, GiftMessage, giftOptions, giftOptionsService) {
    'use strict';

    return Component.extend({
        defaults: {
            displayArea: 'after_details',
            template: 'Magento_GiftMessage/custom-gift-message-item-level'
        },
		model: {},
		recipient: ko.observable(),      
		sender: ko.observable(),
		message: ko.observable(),
        showBox: ko.observable(false),
		toggleVisibility: function () { 
			this.showBox(!this.showBox());
		},
		formBlockVisibility: null,
        resultBlockVisibility: null,
        initialize: function () {
            var self = this,
                model;
            this._super();
			
			
            this.itemId = this.itemId || 'orderLevel';
            model = new GiftMessage(this.itemId);
			
			console.log(model);
            giftOptions.addOption(model);
            this.model = model;

            this.model.getObservable('isClear').subscribe(function (value) {
                if (value == true) { //eslint-disable-line eqeqeq
                    self.formBlockVisibility(false);
                    self.model.getObservable('alreadyAdded')(true);
                }
            });

        },
        getObservable: function (key) {
            return this.model.getObservable(key);
        },
        deleteOptions: function () {
            giftOptionsService(this.model, true);
        },
        isActive: function () {
            return this.model.isGiftMessageAvailable();
        },
        submitOptions: function () {
            giftOptionsService(this.model);
        },
		hideFormBlock: function () {
            this.showBox(false);
        },
    });
});
