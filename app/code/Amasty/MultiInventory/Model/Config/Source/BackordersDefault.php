<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Config\Source;

class BackordersDefault implements \Magento\Framework\Option\ArrayInterface
{
    const USE_PRODUCT_BACKORDERS = 0;
    const USE_WAREHOUSE_BACKORDERS = 1;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::USE_PRODUCT_BACKORDERS, 'label' => __('Product value')],
            ['value' => self::USE_WAREHOUSE_BACKORDERS, 'label' => __('Warehouse value')]
        ];
    }
}
