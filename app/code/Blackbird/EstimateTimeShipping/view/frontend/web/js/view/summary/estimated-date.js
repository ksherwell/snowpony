/**
 * Blackbird EstimateTimeShipping Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_EstimateTimeShipping
 * @copyright       Copyright (c) 2018 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://store.bird.eu/license/
 * @support         help@bird.eu
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/summary/abstract-total'
], function (
    $,
    _,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Blackbird_EstimateTimeShipping/summary/estimated-date'
        },

        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        },

        getValue: function () {
            var div = $('#estimated-delivery-date');
            $.ajax({
                type: "POST",
                url: '/estimatetimeshipping/estimation/quoteDate',
                global: false,
                data: {
                    type: 'cart',
                    isAjax: true
                },
                success: function (data) {
                    if (data.checkoutDisplay == 'all') {
                        if (+data.display && !data.dateExist) {
                            div.addClass('message');
                            div.text(data.preparationDate);
                            div.removeClass('success');
                            div.addClass('notice');
                        } else if (data.dateExist) {
                            div.addClass('message');
                            div.text(data.preparationDate);
                            div.addClass('success');
                        }
                    }
                }
            })
        }
    });
});
