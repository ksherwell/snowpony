<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse;

class Lowstock extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Amasty_MultiInventory';
        $this->_controller = 'amasty_multi_inventory_lowstock';
        $this->_headerText = __('Low stock');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
