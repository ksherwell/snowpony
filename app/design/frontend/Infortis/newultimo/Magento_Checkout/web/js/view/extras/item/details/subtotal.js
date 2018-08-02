/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total'
], function (viewModel) {
    'use strict';

    return viewModel.extend({
        defaults: {
            displayArea: 'after_details',
            template: 'Magento_Checkout/summary/item/details/subtotal'
        },

        /**
         * @param {Object} quoteItem
         * @return {*|String}
         */
        getValue: function (quoteItem) {
            return quoteItem['special_price'] ? this.getFormattedPrice(quoteItem['special_price']) : this.getFormattedPrice(quoteItem['price']);
        },
        getPrice: function (quoteItem) {
            return this.getFormattedPrice(quoteItem['price']);
        },
        getSpecialPrice: function (quoteItem) {
            return this.getFormattedPrice(quoteItem['special_price']);
        }
    });
});
