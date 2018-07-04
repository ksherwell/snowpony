<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseItemRepositoryInterface
{

    /**
     * Save item.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseItemInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseItemInterface $warehouseItem);

    /**
     * Add stock.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseItemInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addStock(Data\WarehouseItemInterface $warehouseItem);

    /**
     * @param \Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function addStockSku(\Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface $warehouseItem);

    /**
     * Retrieve item.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($itemId);

    /**
     * Load Stock item By Product ID and Warehouse ID
     *
     * @param int $productId
     * @param int $warehouseId
     *
     * @return \Amasty\MultiInventory\Model\Warehouse\Item
     */
    public function getByProductWarehouse($productId, $warehouseId);

    /**
     * Delete item.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseItemInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseItemInterface $item);

    /**
     * Delete item by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);

    /**
     * Get stocks for product.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStocks($id);

    /**
     * Get stocks for product.
     *
     * @param string $sku
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStocksSku($sku);

    /**
     * Get products for warehouse.
     *
     * @param string $code
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts($code);
}
