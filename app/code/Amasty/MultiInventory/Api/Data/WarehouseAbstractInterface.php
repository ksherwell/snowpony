<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseAbstractInterface
{
    const ID = 'id';
    const WAREHOUSE_ID = 'warehouse_id';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getWarehouseId();

    /**
     * @param $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param $id
     * @return $this
     */
    public function setWarehouseId($id);
}
