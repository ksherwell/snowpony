<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\WarehouseAbstractInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class AbstractWarehouse extends AbstractExtensibleModel implements WarehouseAbstractInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse
     */
    public $warehouse;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    public $warehouseFactory;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setWarehouseId($id)
    {
        $this->setData(self::WAREHOUSE_ID, $id);
        return $this;
    }

    /**
     * @param Warehouse $warehouse
     * @return $this
     */
    public function setWarehouse(\Amasty\MultiInventory\Model\Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
        $this->setWarehouseId($warehouse->getId());
        return $this;
    }

    /**
     * @return Warehouse|null
     */
    public function getWarehouse()
    {
        if ($this->warehouse === null && ($warehouseId = $this->getWarehouseId())) {
            $warehouse = $this->warehouseFactory->create();
            $warehouse->load($warehouseId);
            $this->setWarehouse($warehouse);
        }

        return $this->warehouse;
    }
}