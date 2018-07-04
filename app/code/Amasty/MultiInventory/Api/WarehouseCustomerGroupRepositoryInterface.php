<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseCustomerGroupRepositoryInterface
{
    /**
     * Save customer group.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseCustomerGroupInterface $item);

    /**
     * Retrieve customer group.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete customer group.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseCustomerGroupInterface $item);

    /**
     * Delete customer group by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
