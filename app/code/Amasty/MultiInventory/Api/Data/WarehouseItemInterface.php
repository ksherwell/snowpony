<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseItemInterface extends WarehouseAbstractInterface
{
    const ITEM_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const QTY = 'qty';
    const AVAILABLE_QTY = 'available_qty';
    const SHIP_QTY = 'ship_qty';
    const ROOM_SHELF = 'room_shelf';
    const STOCK_STATUS = 'stock_status';
    const BACKORDERS = 'backorders';

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @return float
     */
    public function getQty();

    /**
     * @return float
     */
    public function getAvailableQty();

    /**
     * @return float
     */
    public function getShipQty();

    /**
     * @return string
     */
    public function getRoomShelf();

    /**
     * @param int $id
     * @return $this
     */
    public function setProductId($id);

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * @param float $qty
     * @return $this
     */
    public function setAvailableQty($qty);

    /**
     * @param float $qty
     * @return $this
     */
    public function setShipQty($qty);

    /**
     * @param string $text
     * @return $this
     */
    public function setRoomShelf($text);

    /**
     * @return int
     */
    public function getStockStatus();

    /**
     * @param int $stockStatus
     *
     * @return $this
     */
    public function setStockStatus($stockStatus);

    /**
     * @return int
     */
    public function getBackorders();

    /**
     * @param int $backorders
     *
     * @return $this
     */
    public function setBackorders($backorders);
}
