<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Import\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Identifier implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('SKU'),
                'value' => 0,
            ],
            [
                'label' => __('ID'),
                'value' => 1,
            ]
        ];

        return $options;
    }
}
