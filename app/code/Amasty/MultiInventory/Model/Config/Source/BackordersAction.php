<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Config\Source;

class BackordersAction implements \Magento\Framework\Option\ArrayInterface
{
    const SPLIT = 0;
    const DO_NOT_SPLIT = 1;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SPLIT, 'label' => __('Availability first')],
            ['value' => self::DO_NOT_SPLIT, 'label' => __('Backorders first')]
        ];
    }
}
