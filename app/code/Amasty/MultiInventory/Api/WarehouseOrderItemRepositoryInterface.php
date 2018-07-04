<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseOrderItemRepositoryInterface
{

    /**
     * Save item.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseOrderItemInterface $warehouseItem);

    /**
     * Retrieve item.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($itemId);

    /**
     * @param int $orderItemId
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface
     */
    public function getByOrderItemId($orderItemId);

    /**
     * Delete item.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseOrderItemInterface $item);

    /**
     * Delete item by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
