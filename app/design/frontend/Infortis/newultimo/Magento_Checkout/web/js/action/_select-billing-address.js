/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    '../model/quote'
], function ($, quote) {
    'use strict';

    return function (billingAddress) {
        var address = null;

        if (quote.shippingAddress() && billingAddress.getCacheKey() == //eslint-disable-line eqeqeq
            quote.shippingAddress().getCacheKey()
        ) {
            address = $.extend({}, billingAddress);
            address.saveInAddressBook = null;
			//$('#co-shipping-method-form').find('.action.continue').trigger( 'click' );
        } else {
            address = billingAddress;
        }
        quote.billingAddress(address);

    };
});
