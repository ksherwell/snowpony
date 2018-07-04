<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseRepositoryInterface
{
    /**
     * Save warehouse.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseInterface $warehouse
     * @return \Amasty\MultiInventory\Api\Data\WarehouseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseInterface $warehouse);

    /**
     * Retrieve warehouse.
     *
     * @param int $warehouseId
     * @return \Amasty\MultiInventory\Api\Data\WarehouseInterface|\Amasty\MultiInventory\Model\Warehouse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($warehouseId);

    /**
     * Retrieve warehouse.
     *
     * @param string $warehouseCode
     * @return \Amasty\MultiInventory\Api\Data\WarehouseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByCode($warehouseCode);

    /**
     * Delete warehouse.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseInterface $warehouse
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseInterface $warehouse);

    /**
     * Delete warehouse by ID.
     *
     * @param int $warehouseId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($warehouseId);
}
