<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Warehouse', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse');
    }

}
