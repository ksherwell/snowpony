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
 * Class DateFormat
 * @package Blackbird\EstimateTimeShipping\Model\Config\Source
 */
class DateFormat implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \IntlDateFormatter::SHORT  => 'Short (ex: 26/01/18)',
            \IntlDateFormatter::MEDIUM => 'Medium (ex: 26 jan 2018)',
            \IntlDateFormatter::LONG   => 'Long (ex: 26 january 2018)'
        ];
    }
}
