<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

class TotalAvailable extends AbstractColumn
{
    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        return (int)$this->getTotalQty($item['warehouse_id'], 'available_qty');
    }
}
