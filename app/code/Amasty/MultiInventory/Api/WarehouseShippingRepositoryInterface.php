<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseShippingRepositoryInterface
{
    /**
     * Save shipping.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseShippingInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseShippingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseShippingInterface $item);

    /**
     * Retrieve shipping.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseShippingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete shipping.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseShippingInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseShippingInterface $item);

    /**
     * Delete shipping by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
