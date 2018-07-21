define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/set-shipping-information'
], function ($, ko, Component, _, quote, setShippingInformationAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aidalab_DeliveryDateOverride/delivery-date-block',
        },
        isLoading: ko.observable(true),
        isVisible: ko.observable(false),
        shippingDateJs: ko.observable(null),
        shippingDays: ko.observable(null),
        holidays: ko.observableArray([]),
        shippingMethod: ko.observable(quote.shippingMethod()),
        initialize: function () {
            this._super();
            var disabled = window.checkoutConfig.shipping.delivery_date.disabled;
            var additional = window.checkoutConfig.shipping.delivery_date.additional;
            var noday = window.checkoutConfig.shipping.delivery_date.noday;
            var hourMin = parseInt(window.checkoutConfig.shipping.delivery_date.hourMin);
            var hourMax = parseInt(window.checkoutConfig.shipping.delivery_date.hourMax);
            var format = window.checkoutConfig.shipping.delivery_date.format;
            if(!format) {
                format = 'dd-mm-yy';
            }
            var disabledDay = [];
            if (disabled !== null) {
                disabled.split(",").map(function(item) {
                    disabledDay.push(parseInt(item, 10));
                });
            }
            else disabledDay = [0,6];
            var additionalDay = [];
            if (additional !== null) {
                additional.split(',').map(function(item) {
                    additionalDay.push(String(item.replace(/"/g,''), 10));
                });
            }
            var self = this;
            ko.bindingHandlers.datetimepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    //initialize datetimepicker
                    if(noday) {
                        var options = {
                            minDate: 0,
                            dateFormat:format
                        };
                    } else {
                        var options = {
                            showTime: false,
                            showTimepicker: false,
                            showHour: false,
                            showMinute: false,
                            showAnim: "",
                            changeMonth: true,
                            changeYear: true,
                            buttonImageOnly: null,
                            buttonImage: null,
                            showButtonPanel: false,
                            minDate: 0,
                            autoclose: true,
                            dateFormat:format,
                            keepOpen: false,
                            beforeShowDay: function(date) {

                                //check holidays
                                if(self.holidays().length > 0){
                                    var isHoliday = false;
                                    _.each(self.holidays(), function (holiday) {
                                        var parsedDate = jQuery.datepicker.formatDate('dd-mm-yy', date);
                                        if(parsedDate == holiday){
                                            isHoliday = true;
                                        }
                                    });
                                    if(isHoliday){
                                        return [false];
                                    }
                                }

                                //check minimal delivery date
                                if(self.shippingDateJs()){
                                    var estimatedDate = new Date(self.shippingDateJs() * 1000).setHours(0,0,0,0);
                                    if(date < estimatedDate){
                                        return [false];
                                    }
                                }
                                var calendarDate = date.getDay();
                                var shippingDays = self.shippingDays().split(',');

                                //check days
                                if(shippingDays.indexOf(calendarDate.toString()) >= 0 ){
                                    return [true];
                                }

                                return [false];
                            },
                            onSelect: function () {
                                $(this).datetimepicker('hide');
                                setShippingInformationAction();
                            }
                        };
                    }

                    $el.datetimepicker(options);
                },
            };

            var self = this;

            $(document).on('customer-data-reload', function () {
                if(quote.shippingMethod() && self.shippingMethod() != quote.shippingMethod().method_code){
                    self.shippingMethod(quote.shippingMethod().method_code);
                    self.getEstimateDeliveryDate();
                }
            });


            $(document).on('blur', '#delivery_comment', function () {
                setShippingInformationAction();
            });

            $(document).on('change', '[name="authority_to"]', function () {
                setShippingInformationAction();
            });

            return this;
        },

        getEstimateDeliveryDate: function () {
            var self = this;
            self.isLoading(true);
            $('#delivery_date').val('');
            $.ajax({
                type: "POST",
                url: '/estimatetimeshipping/estimation/quoteDate',
                global: false,
                data: {
                    type: 'cart',
                    isAjax: true
                },
                success: function (data) {
                    if (+data.display && !data.dateExist) {
                        self.isVisible(false);
                    } else if (data.dateExist) {
                        self.isVisible(true);
                        self.shippingDateJs(data.shippingDateJs);
                        self.shippingDays(data.shippingDays);
                        self.holidays(data.holidays);
                        $('#delivery_date').removeAttr('disabled');
                        $('[name="authority_to"]').removeAttr('disabled');
                    }
                },
                fail: function (){
                    self.isVisible(false);
                },
                complete: function () {
                    self.isLoading(false);
                }
            })
        }
    });
});
