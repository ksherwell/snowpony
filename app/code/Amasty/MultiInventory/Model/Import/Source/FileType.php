<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Import\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FileType implements OptionSourceInterface
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
                'label' => __('CSV'),
                'value' => 0,
            ],
            [
                'label' => __('XML'),
                'value' => 1,
            ],
            [
                'label' => __('Excel XML'),
                'value' => 2,
            ]
        ];

        return $options;
    }
}
