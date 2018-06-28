<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Warehouse\Item', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item');
    }

    public function addProductFilter($productId)
    {
        $this->addFieldToFilter('wi_product_id', $productId);
        return $this;
    }

    public function addInStockFilter()
    {
        $this->addFieldToFilter('wi_available_quantity', ['gt' => 0]);
        return $this;
    }

    public function joinWarehouse()
    {
        $this->getSelect()->join(
            $this->getTable('bms_advancedstock_warehouse'),
            'w_id = wi_warehouse_id'
        );
        return $this;
    }

    public function addVisibleOnFrontFilter()
    {
        $this->addFieldToFilter('w_display_on_front', 1);
        return $this;
    }

    public function addUnconsistentFilter()
    {
        $conditions = [];
        $conditions[] = "wi_reserved_quantity < wi_physical_quantity and wi_reserved_quantity < wi_quantity_to_ship";
        $conditions[] = "wi_reserved_quantity > wi_quantity_to_ship";
        $conditions[] = "wi_reserved_quantity > wi_physical_quantity";

        $this->getSelect()->where(implode(' OR ', $conditions));

        return $this;
    }

}
