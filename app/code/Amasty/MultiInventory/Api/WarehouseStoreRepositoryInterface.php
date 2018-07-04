<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseStoreRepositoryInterface
{
    /**
     * Save store.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseStoreInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseStoreInterface $item);

    /**
     * Retrieve store.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete store.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseStoreInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseStoreInterface $item);

    /**
     * Delete store by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
