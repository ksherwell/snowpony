/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['uiComponent'], function (Component) {
    'use strict';

    var imageData = window.checkoutConfig.imageData;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/extras/item/details/thumbnail'
        },
        displayArea: 'before_details',
        imageData: imageData,

        /**
         * @param {Object} item
         * @return {Array}
         */
        getImageItem: function (item) {
            if (this.imageData[item['entity_id']]) {
                return this.imageData[item['entity_id']];
            }

            return [];
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getSrc: function (item) {
            if (this.imageData[item['entity_id']]) {
                return this.imageData[item['entity_id']].src;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getWidth: function (item) {
            if (this.imageData[item['entity_id']]) {
                return this.imageData[item['entity_id']].width;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getHeight: function (item) {
            if (this.imageData[item['entity_id']]) {
                return this.imageData[item['entity_id']].height;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getAlt: function (item) {
            if (this.imageData[item['entity_id']]) {
                return this.imageData[item['entity_id']].alt;
            }

            return null;
        }
    });
});
