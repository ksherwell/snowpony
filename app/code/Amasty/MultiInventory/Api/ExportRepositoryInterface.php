<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

interface ExportRepositoryInterface
{
    /**
     * Save Export.
     *
     * @param \Amasty\MultiInventory\Api\Data\ExportInterface $item
     * @return \Amasty\MultiInventory\Api\Data\ExportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\ExportInterface $item);

    /**
     * Retrieve store.
     *
     * @param int $id
     * @return \Amasty\MultiInventory\Api\Data\ExportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete Export.
     *
     * @param \Amasty\MultiInventory\Api\Data\ExportInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\ExportInterface $item);

    /**
     * Delete Export by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
