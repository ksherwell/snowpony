<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface WarehouseQuoteItemRepositoryInterface
{

    /**
     * Save item.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface $item
     * @return \Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\WarehouseQuoteItemInterface $warehouseItem);

    /**
     * Retrieve item.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($itemId);

    /**
     * Delete item.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\WarehouseQuoteItemInterface $item);

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
     * @param \Magento\Quote\Api\Data\CartItemInterface $quoteItem
     *
     * @return int|null
     */
    public function getWarehouseIdByItem($quoteItem);
}
