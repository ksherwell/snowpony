<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;

/**
 * Abstract action reindex class
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock
 */
abstract class AbstractAction
{
    const MULTI_INVENTORY_TABLE = 'amasty_multiinventory_warehouse_item';

    const INVENTORY_TABLE = 'cataloginventory_stock_item';

    private $updateStockStatus = false;

    /**
     * Resource instance
     *
     * @var Resource
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $helper;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractAction constructor.
     *
     * @param ResourceConnection                        $resource
     * @param WarehouseFactory                          $warehouseFactory
     * @param \Amasty\MultiInventory\Helper\System      $helper
     * @param \Magento\Framework\Indexer\CacheContext   $cacheContext
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param LoggerInterface                           $logger
     */
    public function __construct(
        ResourceConnection $resource,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        \Amasty\MultiInventory\Helper\System $helper,
        \Magento\Framework\Indexer\CacheContext $cacheContext,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->eventManager = $eventManager;
        $this->warehouseFactory = $warehouseFactory;
        $this->cacheContext = $cacheContext;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     *
     * @return void
     */
    abstract public function execute($ids);

    /**
     * @return AdapterInterface
     */
    public function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param $entityName
     * @return mixed
     */
    public function getTable($entityName)
    {
        return $this->resource->getTableName($entityName);
    }

    /**
     * Reindex all
     *
     * @return void
     */
    public function reindexAll()
    {
        $this->getConnection()->beginTransaction();
        try {
            $select = $this->scopeQuery();
            $this->insert($select);
            $this->updateInventory($select);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
        }
        if ($this->helper->isMultiEnabled()) {
            $this->createEvent();
        }

        return $this;
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function reindexRows($productIds = [])
    {
        $this->getConnection()->beginTransaction();
        try {
            if (!is_array($productIds)) {
                $productIds = [$productIds];
            }
            $select = $this->scopeQuery();
            if (count($productIds) > 0) {
                $select->where('main_table.product_id IN(?)', $productIds);
            }
            $this->insert($select);
            $this->updateInventory($select);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->logger->error($e);

            $this->getConnection()->rollBack();
        }

        if ($this->helper->isMultiEnabled()) {
            $this->createEvent($productIds);
        }

        return $this;
    }

    /**
     * @return Select
     */
    private function scopeQuery()
    {
        $defaultId = $this->warehouseFactory->create()->getDefaultId();
        $warehousesNotActive = $this->warehouseFactory->create()->getWhNotActive();
        $warehousesNotActive[] = $defaultId;
        $arrayFields = ['qty', 'available_qty', 'ship_qty'];
        $columnArray['product_id'] = 'product_id';
        foreach ($arrayFields as $field) {
            $columnArray[$field] = new \Zend_Db_Expr(sprintf('SUM(%s)', $field));
        }
        $columnArray['warehouse_id'] = new \Zend_Db_Expr(sprintf('ABS(%s)', $defaultId));
        $columnArray[WarehouseItemInterface::STOCK_STATUS] = new \Zend_Db_Expr(
            sprintf('MAX(%s)', WarehouseItemInterface::STOCK_STATUS)
        );

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getTable(self::MULTI_INVENTORY_TABLE)],
            $columnArray
        );
        $select->group('main_table.product_id');
        $select->order('main_table.product_id');
        $select->where('main_table.warehouse_id NOT IN(?)', $warehousesNotActive);

        return $select;
    }

    /**
     * @param Select $query
     */
    private function insert($query)
    {
        $query = $this->getConnection()->insertFromSelect(
            $query,
            $this->getTable(self::MULTI_INVENTORY_TABLE),
            ['product_id', 'qty', 'available_qty', 'ship_qty', 'warehouse_id', WarehouseItemInterface::STOCK_STATUS],
            AdapterInterface::INSERT_ON_DUPLICATE
        );
        $this->getConnection()->query($query);
    }

    /**
     * @param Select $query
     */
    private function updateInventory(Select $query)
    {
        if ($this->helper->isMultiEnabled()) {
            $field = 'qty';
            if ($this->helper->getAvailableDecreese()) {
                $field = 'available_qty';
            }
            $query->reset(Select::COLUMNS);
            $query->columns([
                'stock_id' => new \Zend_Db_Expr(Stock::DEFAULT_STOCK_ID),
                'product_id',
                'qty' => new \Zend_Db_Expr(sprintf('SUM(%s)', $field)),
                'is_in_stock' => new \Zend_Db_Expr(sprintf('MAX(%s)', WarehouseItemInterface::STOCK_STATUS))
            ]);

            $query = $this->getConnection()->insertFromSelect(
                $query,
                $this->getTable(self::INVENTORY_TABLE),
                ['stock_id', 'product_id', 'qty', 'is_in_stock'],
                AdapterInterface::INSERT_ON_DUPLICATE
            );

            $this->getConnection()->query($query);
        }
    }

    /**
     * @param array $productIds
     */
    public function createEvent($productIds = [])
    {
        $this->cacheContext->registerEntities(Warehouse::CACHE_TAG, $productIds);
        $this->eventManager->dispatch('amasty_multi_inventory_indexer', ['object' => $this->cacheContext]);
    }

    /**
     * @param boolean $status
     */
    public function setUpdateStockStatus($status)
    {
        $this->updateStockStatus = $status;
    }
}
