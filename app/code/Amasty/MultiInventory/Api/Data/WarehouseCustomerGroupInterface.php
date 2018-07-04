<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseCustomerGroupInterface extends WarehouseAbstractInterface
{
    const GROUP_ID = 'group_id';

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param $id
     * @return int
     */
    public function setGroupId($id);
}
