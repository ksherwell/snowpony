<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Import;

use Magento\Framework\View\Element\UiComponentInterface;

class Warehouse extends \Amasty\MultiInventory\Ui\Component\Listing\Column\AbstractColumn
{

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        return $this->repository->getById($item['warehouse_id'])->getTitle();
    }
}
