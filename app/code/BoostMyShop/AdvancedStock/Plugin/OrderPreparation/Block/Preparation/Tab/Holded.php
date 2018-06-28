<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class Holded extends AbstractTab
{

    public function aroundAddWarehouseFilter(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\Holded $subject, $proceed, $collection, $warehouseId)
    {
        $collection->addFieldToFilter('entity_id', ['in' => $this->getOpenedOrderIdForWarehouse($warehouseId)]);
    }

}
