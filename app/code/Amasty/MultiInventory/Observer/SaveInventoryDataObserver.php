<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\DecoderInterface;

class SaveInventoryDataObserver implements ObserverInterface
{

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $factory;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;
    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $stockRepository;
    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    public function __construct(
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $itemFactory,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $stockRepository,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Amasty\MultiInventory\Helper\System $system,
        DecoderInterface $jsonDecoder,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
    ) {
        $this->factory = $factory;
        $this->itemFactory = $itemFactory;
        $this->repository = $repository;
        $this->system = $system;
        $this->jsonDecoder = $jsonDecoder;
        $this->stockRepository = $stockRepository;
        $this->processor = $processor;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->system->isMultiEnabled()) {
            return $this;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getData('warehouses')) {
            $warehouses = $this->jsonDecoder->decode($product->getData('warehouses'));
            $defaultId = $this->factory->create()->getDefaultId();
            $allWarehouses = $this->factory->create()->getCollection()
                ->addFieldToFilter('warehouse_id', ['neq' => $defaultId])
                ->addFieldToFilter('manage', 1)
                ->getAllIds();
            if (count($warehouses)) {
                $remIds = $this->remove(array_diff($allWarehouses, array_keys($warehouses)), $product->getId());
                foreach ($warehouses as $key => $warehouse) {
                    if (!in_array($key, $remIds)) {
                        $this->change($key, $product, $warehouse);
                    }
                }
            }
        }

        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $this->processor->reindexRow($product->getId());
        }

        return $this;
    }

    /**
     * @param array $ids
     * @param $productId
     * @return array
     */
    private function remove(array $ids, $productId)
    {
        if (count($ids)) {
            foreach ($ids as $id) {
                $model = $this->repository->getById($id);
                $stockItem = $this->stockRepository->getByProductWarehouse($productId, $id);
                if ($stockItem->getId()) {
                    $model->addRemoveItem($stockItem);
                    $model->deleteItems($productId);
                }
            }
        }

        return $ids;
    }

    /**
     * @param $key
     * @param \Magento\Catalog\Model\Product $product
     * @param array $warehouse
     */
    private function change($key, $product, $warehouse)
    {
        $model = $this->repository->getById($key);
        if (!$model->getIsGeneral()) {
            /** @var \Amasty\MultiInventory\Model\Warehouse\Item $object */
            $object = $this->itemFactory->create()
                ->setProductId($product->getId())
                ->setQty($warehouse['qty'])
                ->setRoomShelf($warehouse['room_shelf'])
                ->setBackorders($warehouse['backorders'])
                ->setStockStatus($warehouse['stock_status']);

            $model->addItem($object);
        }
    }
}
