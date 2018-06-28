<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class InStock extends AbstractTab
{


    public function aroundAddAdditionnalFilters(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock $subject, $proceed, $collection)
    {
        $backOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addProductTypeFilter()->addQtyToShipFilter()->addNotFullyReservedFilter()->getOrderIds();
        if (count($backOrderIds) > 0)
            $collection->addFieldToFilter('entity_id', array('nin' => $backOrderIds));

        $toShipOrderIds = $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addProductTypeFilter()->addQtyToShipFilter()->getOrderIds();
        if (count($toShipOrderIds) > 0)
            $collection->addFieldToFilter('entity_id', array('in' => $toShipOrderIds));
    }


    public function aroundAddWarehouseFilter(\BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock $subject, $proceed, $collection, $warehouseId)
    {
        $collection->addFieldToFilter('entity_id', ['in' => $this->getOpenedOrderIdForWarehouse($warehouseId)]);
    }

}
