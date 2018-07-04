<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Product;

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
        if ($this->helper->isMultiEnabled()) {
            $content = $this->jsonEncoder->encode($this->getWarehouses($item['entity_id']));
        } else {
            $content = $this->jsonEncoder->encode($this->getInventory($item['entity_id']));
        }

        return $content;
    }
}
