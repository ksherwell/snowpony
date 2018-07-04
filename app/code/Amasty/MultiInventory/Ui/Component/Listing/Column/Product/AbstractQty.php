<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Product;

class AbstractQty extends \Amasty\MultiInventory\Ui\Component\Listing\Column\AbstractColumn
{
    /**
     * @param $qty
     * @return int|string
     */
    protected function changeQty($qty)
    {
        if ($qty == null) {
            $qty = "N/A";
        } else {
            $qty = (int)$qty;
        }

        return $qty;
    }
}
