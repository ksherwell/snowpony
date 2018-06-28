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

namespace Blackbird\EstimateTimeShipping\Model\PublicHoliday\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DateType
 * @package Blackbird\EstimateTimeShipping\Model\PublicHoliday\Source
 */
class DateType implements ArrayInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => 'Fixed Date'
            ],
            [
                'value' => 1,
                'label' => 'Variable Day'
            ]
        ];
    }
}
