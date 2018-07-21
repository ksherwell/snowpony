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
            template: 'Magento_Checkout/summary/item/details'
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
		increaseCounter : function() {
            /* var previousCount = this.numberOfClicks();
            this.numberOfClicks(previousCount + 1); */
			console.log('qty');
        },
		decreaseCounter : function() {
            /* var previousCount = this.numberOfClicks();
            this.numberOfClicks(previousCount + 1); */
			console.log('qty');
        },
		deleteItem : function(id) {
            /* var previousCount = this.numberOfClicks();
            this.numberOfClicks(previousCount + 1); */
			console.log(id);
        }
    });
});
