<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseQuoteItemInterface extends WarehouseAbstractInterface
{
    const ITEM_ID = 'item_id';
    const QUOTE_ID = 'quote_id';
    const QUOTE_ITEM_ID = 'quote_item_id';
    const QTY = 'qty';

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @return int
     */
    public function getQuoteItemId();

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param int $id
     * @return $this
     */
    public function setQuoteId($id);

    /**
     * @param int $id
     * @return $this
     */
    public function setQuoteItemId($id);

    /**
     * @param float$qty
     * @return $this
     */
    public function setQty($qty);
}
