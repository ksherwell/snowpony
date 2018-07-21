define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PHPMechanic_DeliveryDate/delivery-date-block'
        },
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
                            showHour: false,
                            showMinute: false,
                            showAnim: "",
                            changeMonth: true,
                            changeYear: true,
                            buttonImageOnly: null,
                            buttonImage: null,
                            showButtonPanel: false,
                            minDate: 0,
                            dateFormat:format,
                            beforeShowDay: function(date) {
                                var day = date.getDay();
                                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                var myDisabledCustom = (jQuery.inArray(string, additionalDay) != -1);
                                var myDisabledDays = (jQuery.inArray(day, disabledDay) != -1);
                                return [!myDisabledDays && !myDisabledCustom];
                                // var day = date.getDay();
                                // console.log(string);
                                // if(disabledDay.indexOf(day) > -1) {
                                //     return [false];
                                // } else {
                                //     return [true];
                                // }
                            }
                        };
                    }

                    $el.datetimepicker(options);

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datetimepicker) {
                            writable = propWriters.datetimepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datetimepicker("getDate"));
                },
                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    //when the view model is updated, update the widget
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }
                }
            };

            return this;
        }
    });
});
