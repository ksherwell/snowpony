<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseImportRepositoryInterface
{
    /**
     * Save Import.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseImportInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseImportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseImportInterface $item);

    /**
     * Retrieve store.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseImportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete import.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseImportInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseImportInterface $item);

    /**
     * Delete import by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
