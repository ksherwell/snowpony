<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Manage implements OptionSourceInterface
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
                'label' => __('No'),
                'value' => 0,
            ],
            [
                'label' => __('Yes'),
                'value' => 1,
            ]
        ];

        return $options;
    }
}
