<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseImportInterface extends WarehouseAbstractInterface
{
    const ITEM_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const QTY = 'qty';
    const NEW_QTY = 'new_qty';
    const IMPORT_NUMBER = 'import_number';

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
    public function getNewQty();

    /**
     * @return int
     */
    public function getImportNumber();

    /**
     * @param $id
     */
    public function setProductId($id);

    /**
     * @param $qty
     */
    public function setQty($qty);

    /**
     * @param $qty
     */
    public function setNewQty($qty);

    /**
     * @param $number
     */
    public function setImportNumber($number);
}
