<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseStoreInterface extends WarehouseAbstractInterface
{
    const STORE_ID = 'store_id';

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param $id
     * @return int
     */
    public function setStoreId($id);
}
