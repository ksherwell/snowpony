<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Item extends AbstractDb
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $factory;
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_multiinventory_warehouse_item', 'item_id');
    }

    public function __construct(
        Context $context,
        EntityManager $entityManager,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Magento\Framework\Indexer\CacheContext $cacheContext,
        ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->productCollection = $productCollection;
    }

    /**
     * @param AbstractModel $object
     * @param int|string $value
     * @param string|null $field
     * @return \Amasty\MultiInventory\Model\Warehouse\Item
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
    }

    /**
     * @param \Amasty\MultiInventory\Model\Warehouse\Item $object
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        if ($object->isStockStatusChanged()) {
            $this->cleanCache($object);
        }

        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }

    /**
     * @param int    $warehouseId
     * @param string $field
     *
     * @return string
     */
    public function getTotalQty($warehouseId, $field = 'qty')
    {
        $select = $this->getStockSelect($warehouseId);
        $select->columns(['size' => sprintf('SUM(%s)', $field)]);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * If don't have assigned warehouses for product, will return 1. Because default warehouse
     *
     * @param int|null $warehouseId
     * @param int|null $productId
     *
     * @return string
     */
    public function getTotalSku($warehouseId = null, $productId = null)
    {
        $select = $this->getStockSelect($warehouseId, $productId);
        $select->columns(['size' => 'COUNT(product_id)']);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int      $productId
     * @param int|null $warehouseId
     *
     * @return array
     */
    public function getProductStockData($productId, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = $this->factory->create()->getDefaultId();
        }
        $select = $this->getStockSelect($warehouseId, $productId);
        $select->columns();

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param null $warehouseId
     * @param null $productId
     *
     * @return \Magento\Framework\DB\Select
     */
    private function getStockSelect($warehouseId = null, $productId = null)
    {
        $select = $this->getConnection()->select()->from(
            ['wi' => $this->getMainTable()],
            []
        );

        if ($productId !== null) {
            $select->where('wi.product_id = ?', $productId);
        }

        if ($warehouseId !== null) {
            $select->where('wi.warehouse_id = ?', $warehouseId);
        }

        return $select;
    }

    /**
     * @param $productId
     * @return array
     */
    public function getItems($productId)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getMainTable()],
            ['w.warehouse_id', 'wh.title', 'w.qty']
        )->where(
            'w.product_id = :product_id'
        )->joinLeft(
            ['wh' => $this->getTable('amasty_multiinventory_warehouse')],
            'wh.warehouse_id = w.warehouse_id',
            ['wh.title']
        )->where(sprintf('wh.manage=%s and w.warehouse_id<>%s', 1, $this->factory->create()->getDefaultId()));
        $bind = ['product_id' => (int)$productId];

        return $this->getConnection()->fetchAssoc($select, $bind);
    }

    /**
     * @return array
     */
    public function getItemsExport()
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getMainTable()],
            ['cpe.sku', 'wh.warehouse_id', 'wh.code', 'w.qty']
        )
            ->joinLeft(
                ['wh' => $this->getTable('amasty_multiinventory_warehouse')],
                'wh.warehouse_id = w.warehouse_id'
            )
            ->joinLeft(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'cpe.entity_id = w.product_id'
            );
        $bind = [];

        return $this->getConnection()->fetchAll($select, $bind);
    }

    /**
     * @return int
     */
    public function deleteItems()
    {
        return $this->getConnection()->delete($this->getMainTable());
    }

    protected function cleanCache($object)
    {
        $select = $this->productCollection->getSelect()->reset()
            ->distinct(true)
            ->from($this->productCollection->getTable('catalog_category_product'), ['category_id'])
            ->where('product_id = ?', $object->getProductId());
        $affectedCategories = $this->productCollection->getConnection()->fetchCol($select);

        $this->cacheContext->registerEntities(Category::CACHE_TAG, $affectedCategories);
        $this->cacheContext->registerEntities(Product::CACHE_TAG, [$object->getProductId()]);

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
