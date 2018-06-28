<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model\Pdf;

class Order
{

    protected $_warehouseFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
    ) {
        $this->_warehouseFactory = $warehouseFactory;
    }

    public function aroundGetShippingAddress(\BoostMyShop\Supplier\Model\Pdf\Order $subject, $proceed, $order)
    {
        $warehouse = $this->_warehouseFactory->create()->load($order->getpo_warehouse_id());
        return $warehouse->getAddress();

    }


}
