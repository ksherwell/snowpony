<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer;

class Warehouse implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var Warehouse\Action\Row
     */
    private $productStockIndexerRow;

    /**
     * @var Warehouse\Action\Rows
     */
    private $productStockIndexerRows;

    /**
     * @var Warehouse\Action\Full
     */
    private $productStockIndexerFull;

    /**
     * Warehouse constructor.
     * @param Warehouse\Action\Row $productStockIndexerRow
     * @param Warehouse\Action\Rows $productStockIndexerRows
     * @param Warehouse\Action\Full $productStockIndexerFull
     */
    public function __construct(
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Row $productStockIndexerRow,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Rows $productStockIndexerRows,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Full $productStockIndexerFull
    ) {
        $this->productStockIndexerRow = $productStockIndexerRow;
        $this->productStockIndexerRows = $productStockIndexerRows;
        $this->productStockIndexerFull = $productStockIndexerFull;
    }

    /**
     * @param \int[] $ids
     */
    public function execute($ids)
    {
        $this->productStockIndexerRows->execute($ids);
    }

    /**
     *
     * @return void
     */
    public function executeFull()
    {
        $this->productStockIndexerFull->execute();
    }

    /**
     * @param array $ids
     */
    public function executeList(array $ids)
    {
        $this->productStockIndexerRows->execute($ids);
    }

    /**
     * @param int $id
     */
    public function executeRow($id)
    {
        $this->productStockIndexerRow->execute($id);
    }
}
