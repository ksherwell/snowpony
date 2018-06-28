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

define(
    [
        'jquery',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/checkout-data',
        'mage/translate'
    ],
    function ($, customerData, checkoutData) {
        return function (config) {
            var dateForm = {
                options: {
                    divId: config.divId,
                    type: config.type,
                    currentSku: config.currentSku
                },

                callAjax: function () {
                    var div = $(this.options.divId);
                    if ($.active <= 1) {
                        $.ajax({
                            type: "POST",
                            url: '/estimatetimeshipping/estimation/quoteDate',
                            global: false,
                            data: {
                                currentSku: this.options.currentSku,
                                type: this.options.type,
                                qty: $('#qty').val(),
                                address: JSON.stringify(this._getCartData()),
                                method: checkoutData.getSelectedShippingRate(),
                                isAjax: true
                            },
                            success: function (data) {
                                if (+data.display && !data.dateExist) {
                                    div.text(data.preparationDate);
                                    div.removeClass('success');
                                    div.addClass('notice');
                                } else if (data.dateExist) {
                                    div.text(data.preparationDate);
                                    div.addClass('success');
                                }
                            }
                        })
                    }
                },

                _getCartData: function () {
                    return {
                        'region_id': this._changeUndefined($('select[name="region_id"]').val()),
                        'country_id': this._changeUndefined($('select[name="country_id"]').val()),
                        'region': this._changeUndefined($('input[name="region"]').val()),
                        'postcode': this._changeUndefined($('input[name="postcode"]').val())
                    }
                },

                _changeUndefined: function (val) {
                    if (val === undefined) {
                        return '';
                    }
                    return val;
                }
            };

            $(document).on('customer-data-reload', function () {
                dateForm.callAjax();
            });

            $(document).on('change', 'select[name="region_id"]', function () {
                dateForm.callAjax();
            });

            $(document).on('change', 'select[name="country_id"]', function () {
                dateForm.callAjax();
            });

            $(document).on('change', 'input[name="region"]', function () {
                dateForm.callAjax();
            });

            $(document).on('change', 'input[name="postcode"]', function () {
                dateForm.callAjax();
            });

            $(document).on('change', '.radio', function () {
                dateForm.callAjax();
            });


            if (dateForm.options.type == "product") {
                dateForm.callAjax();

                var sections = ['product_data_storage', 'customer', 'cart', 'directory-data', 'checkout-data', 'cart-data'];
                $(document).on('customer-data-reload', function (e, data) {
                    var dataLength = data.length;
                    var matched = false;
                    var i = 0;
                    while (i < dataLength && !matched) {
                        if ($.inArray(data[i], sections) !== -1) {
                            dateForm.callAjax();
                            matched = true;
                        }
                        i++;
                    }
                });

                $('#qty').on('change', function () {
                    dateForm.callAjax();
                });
            }
        }
    }
);