<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseOrderItemInterface extends WarehouseAbstractInterface
{
    const ITEM_ID = 'item_id';
    const ORDER_ID = 'order_id';
    const ORDER_ITEM_ID = 'order_item_id';

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @return int
     */
    public function getOrderItemId();

    /**
     * @param int $id
     * @return $this
     */
    public function setOrderId($id);

    /**
     * @param int $id
     * @return $this
     */
    public function setOrderItemId($id);
}
