<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface as WarehouseRepository;

class ItemRepository implements \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item|\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store
     */
    protected $resource;

    /**
     * @var ItemFactory|StoreFactory
     */
    protected $factory;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    private $stockItems = [];
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $stockCollection;

    /**
     * ItemRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $resource
     * @param ItemFactory $factory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $resource,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $stockCollection,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->processor = $processor;
        $this->warehouseFactory = $warehouseFactory;
        $this->productRepository = $productRepository;
        $this->stockCollection = $stockCollection;
    }

    /**
     * @param Data\WarehouseItemInterface $item
     * @return Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseItemInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * @param Data\WarehouseItemInterface $item
     * @return Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function addStock(Data\WarehouseItemInterface $item)
    {
        $sendItem = null;

        try {
            if (!$item->getId()) {
                $stockItem = $this->getByProductWarehouse($item->getProductId(), $item->getWarehouseId());
                if ($stockItem->getId()) {
                    $stockItem->setQty($item->getQty());
                    $stockItem->setRoomShelf($item->getRoomShelf());
                    $sendItem = $stockItem;
                } else {
                    $sendItem = $item;
                }
            } else {
                $sendItem = $item;
            }

            $sendItem->recalcAvailable();
            $this->resource->save($sendItem);
            $this->setStockItem($sendItem);

            $this->processor->reindexRow($sendItem->getProductId());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $sendItem;
    }

    /**
     * @param Data\WarehouseItemApiInterface
     * @return Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function addStockSku(Data\WarehouseItemApiInterface $item)
    {
        $sendItem = null;
        try {
               $newData  = $item->getItemData();
               $sendItem = $this->addStock($this->factory->create()->setData($newData));

        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $sendItem;
    }

    /**
     * @param $id
     * @return CustomerGroup|Store
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Store with id "%1" does not exist.', $id));
        }
        $this->setStockItem($model);

        return $model;
    }

    /**
     * Load Stock item By Product ID and Warehouse ID
     *
     * @param int $productId
     * @param int $warehouseId
     *
     * @return \Amasty\MultiInventory\Model\Warehouse\Item
     */
    public function getByProductWarehouse($productId, $warehouseId)
    {
        if (!isset($this->stockItems[$warehouseId][$productId])) {
            $this->stockItems[$warehouseId][$productId] = $this->stockCollection->create()
                ->loadProductWarehouse($productId, $warehouseId);
        }

        return $this->stockItems[$warehouseId][$productId];
    }

    /**
     * @param Data\WarehouseItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseItemInterface $item)
    {
        try {
            $productId = $item->getProductId();
            $warehouseId = $item->getWarehouseId();
            if (isset($this->stockItems[$warehouseId][$productId])) {
                unset($this->stockItems[$warehouseId][$productId]);
            }
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the warehouse store: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Get stocks for product.
     *
     * @param int $id
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStocks($id)
    {
        return $this->stockCollection->create()
            ->addFieldToFilter('product_id', $id)
            ->getItems();
    }

    /**
     * Get stocks for product.
     *
     * @param string $sku
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStocksSku($sku)
    {
        if ($id = $this->productRepository->get($sku)->getId()) {
            return $this->getStocks($id);
        }

        return null;
    }

    /**
     * Get products for warehouse.
     *
     * @param string $code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts($code)
    {
        $collectWh = $this->warehouseFactory->create()->getCollection()->addFieldToFilter('code', $code);
        $id = $collectWh->getFirstItem()->getId();
        if ($id) {
            return $this->stockCollection->create()
                ->addFieldToFilter('warehouse_id', $id)
                ->getItems();
        }

        return null;
    }

    /**
     * @param Data\WarehouseItemInterface $stockItem
     */
    private function setStockItem(Data\WarehouseItemInterface $stockItem)
    {
        $this->stockItems[$stockItem->getWarehouseId()][$stockItem->getProductId()] = $stockItem;
    }
}
