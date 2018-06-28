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

var config = {
    map: {
        '*': {
            dateForm: 'Blackbird_EstimateTimeShipping/js/dateForm'
        }
    },
    deps: [
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/checkout-data'
    ]
};
