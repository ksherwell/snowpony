<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Model\ResourceModel\Stock;

use Amasty\MultiInventory\Model\Dispatch;

class StatusPlugin
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item
     */
    private $warehouseStockResource;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var \Magento\Store\Api\StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var bool
     */
    private $isFilterInStock;

    public function __construct(
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $warehouseStockResource,
        Dispatch $dispatch,
        \Magento\Store\Api\StoreResolverInterface $storeResolver,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->system = $system;
        $this->warehouseStockResource = $warehouseStockResource;
        $this->dispatch = $dispatch;
        $this->storeResolver = $storeResolver;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status  $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     */
    public function beforeAddStockDataToCollection($subject, $collection, $isFilterInStock)
    {
        $this->isFilterInStock = $isFilterInStock;
    }

    /**
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status  $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function afterAddStockDataToCollection($subject, $collection)
    {
        if (!$this->system->isMultiEnabled()
            || !$this->system->isLockOnStore()
            || $collection->getFlag('warehouse_index_joined')
        ) {
            return $collection;
        }

        $this->dispatch->setCallables($this->system->getDispatchOrder());
        $this->dispatch->setDirection(Dispatch::DIRECTION_STORE);
        $this->dispatch->searchWh();
        $warehouseIds = $this->dispatch->getWarehousesRaw();
        if (empty($warehouseIds)) {
            return $collection;
        }

        $conditionTemplate = 'e.entity_id = %1$s.product_id AND %1$s.warehouse_id = ?';

        $stockColumn = 0;
        $columns = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::COLUMNS);
        foreach ($columns as $key => $column) {
            if (isset($column[2]) && $column[2] == 'is_salable') {
                $stockColumn = $column[0] . '.' . $column[1];
                unset($columns[$key]);
                break;
            }
        }
        $collection->getSelect()->setPart(\Magento\Framework\DB\Select::COLUMNS, $columns);

        $alias = 'warehouse_index';

        $joinCondition = $this->warehouseStockResource->getConnection()->quoteInto(
            sprintf($conditionTemplate, $alias),
            current($warehouseIds)
        );

        $collection->getSelect()->joinLeft(
            [$alias => $this->warehouseStockResource->getMainTable()],
            $joinCondition,
            []
        );

        $stockColumn = $collection->getConnection()
            ->getIfNullSql('warehouse_index.stock_status', $stockColumn);

        $collection->getSelect()->columns(
            ['is_salable' => $stockColumn]
        );

        $collection->setFlag('warehouse_index_joined', true);

        if ($this->isFilterInStock) {
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            foreach ($where as $key => $column) {
                if (strpos($column, 'stock_status_index.stock_status') !== false) {
                    unset($where[$key]);
                    break;
                }
            }
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
            $collection->getSelect()->where(
                $stockColumn . ' = ?',
                \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
            );
        }

        return $collection;
    }
}
