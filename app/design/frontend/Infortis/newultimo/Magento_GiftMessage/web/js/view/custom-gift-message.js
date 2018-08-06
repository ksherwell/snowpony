/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery',
	'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
		defaults: {
            displayArea: 'gift_details',
            template: 'Magento_GiftMessage/custom-gift-message-item-level'
        },
		recipient: ko.observable(),      
		sender: ko.observable(),
		message: ko.observable(),
		showBox: ko.observable(true),
		toggleVisibility: function () { 
			this.showBox(!this.showBox());
		},
        initialize: function () {
            var self = this,
                model;
        }
    });
});
