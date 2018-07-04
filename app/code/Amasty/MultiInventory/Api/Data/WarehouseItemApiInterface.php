<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseItemApiInterface
{
    const SKU = 'sku';
    const CODE = 'code';
    const QTY = 'qty';
    const AVAILABLE_QTY = 'available_qty';
    const SHIP_QTY = 'ship_qty';
    const ROOM_SHELF = 'room_shelf';

    /**
     * @return string
     */
    public function getSku();

    /**
     * @return string
     */
    public function getCode();

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
     * @param $sku
     */
    public function setSku($sku);

    /**
     * @param $code
     * @return mixed
     */
    public function setCode($code);

    /**
     * @param $qty
     * @return float
     */
    public function setQty($qty);

    /**
     * @param $qty
     * @return float
     */
    public function setAvailableQty($qty);

    /**
     * @param $qty
     * @return float
     */
    public function setShipQty($qty);

    /**
     * @param $text
     * @return string
     */
    public function setRoomShelf($text);
}
