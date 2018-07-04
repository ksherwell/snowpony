<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class WarehouseRepository implements \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
{
    /**
     * @var ResourceModel\Warehouse
     */
    protected $warehouseResource;

    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * WarehouseRepository constructor.
     * @param ResourceModel\Warehouse $warehouseResource
     * @param WarehouseFactory $warehouseFactory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse $warehouseResource,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
    ) {
        $this->warehouseResource = $warehouseResource;
        $this->warehouseFactory = $warehouseFactory;
    }

    /**
     * @param Data\WarehouseInterface $warehouse
     * @return Data\WarehouseInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseInterface $warehouse)
    {
        try {
            $this->warehouseResource->save($warehouse);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $warehouse;
    }

    /**
     * @param int $warehouseId
     * @return Warehouse
     * @throws NoSuchEntityException
     */
    public function getById($warehouseId)
    {
        $warehouse = $this->warehouseFactory->create();
        $this->warehouseResource->load($warehouse, $warehouseId);

        if (!$warehouse->getId()) {
            throw new NoSuchEntityException(__('Warehouse with id "%1" does not exist.', $warehouseId));
        }

        return $warehouse;
    }

    /**
     * @param string $warehouseCode
     * @return Warehouse
     * @throws NoSuchEntityException
     */
    public function getByCode($warehouseCode)
    {
        $warehouse = $this->warehouseFactory->create();
        $this->warehouseResource->load($warehouse, $warehouseCode, Data\WarehouseInterface::CODE);

        if (!$warehouse->getId()) {
            throw new NoSuchEntityException(__('Warehouse with code "%1" does not exist.', $warehouseCode));
        }

        return $warehouse;
    }
    
    /**
     * @param Data\WarehouseInterface $warehouse
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseInterface $warehouse)
    {
        try {
            $this->warehouseResource->delete($warehouse);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the warehouse: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $warehouseId
     * @return bool
     */
    public function deleteById($warehouseId)
    {
        return $this->delete($this->getById($warehouseId));
    }
}
