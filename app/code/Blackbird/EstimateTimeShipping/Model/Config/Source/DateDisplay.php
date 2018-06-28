<?php
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

namespace Blackbird\EstimateTimeShipping\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DateDisplay
 * @package Blackbird\EstimateTimeShipping\Model\Config\Source
 */
class DateDisplay implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            0 => 'Display Date per Product',
            1 => 'Display Only the Last Date'
        ];
    }
}
