/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery', 
    'uiComponent'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/customdetails'
        },
		initialize: function () {
			self = this;
			this._super();
			
			$('.qty-item-group .item-button').click(function(e){
				console.log('qty');
			});
			
			$('.item-remove').click(function(e){
				console.log($(this).attr('dataid'));
			});
			
		},

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
		increaseItem : function(item_id) {
			console.log(item_id);
        },
		decreaseItem : function(item_id) {
			console.log(item_id);
        },
		deleteItem : function(item_id) {
			console.log(item_id);
        }
    });
});
