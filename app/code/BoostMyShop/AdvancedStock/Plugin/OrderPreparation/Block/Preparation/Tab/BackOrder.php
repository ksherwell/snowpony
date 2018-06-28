<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class BackOrder extends AbstractTab
{

    public function aroundAddAdditionnalFilters(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\BackOrder $subject, $proceed, $collection)
    {
        $backOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addProductTypeFilter()->addQtyToShipFilter()->addNotFullyReservedFilter()->getOrderIds();
        if (count($backOrderIds) > 0)
            $collection->addFieldToFilter('entity_id', array('in' => $backOrderIds));
        else
            $collection->addFieldToFilter('entity_id', array('in' => [-1]));
    }

    public function aroundAddWarehouseFilter(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\BackOrder $subject, $proceed, $collection, $warehouseId)
    {
        $collection->addFieldToFilter('entity_id', ['in' => $this->getOpenedOrderIdForWarehouse($warehouseId)]);
    }


}
