<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Helper;

use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;

class Data extends AbstractHelper
{
    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $factory;

    /**
     * @var System
     */
    private $system;

    /**
     * @var \Amasty\MultiInventory\Model\Dispatch
     */
    private $dispatch;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    /**
     * @var \Amasty\MultiInventory\Model\EmailNotification
     */
    private $emailNotification;

    /**
     * @var \Magento\Framework\File\Size
     */
    private $fileSize;

    /**
     * @var \Amasty\MultiInventory\Logger\Logger
     */
    private $logger;
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory
     */
    private $quoteItemWhCollection;

    /**
     * Data constructor.
     *
     * @param \Amasty\MultiInventory\Model\WarehouseFactory                    $factory
     * @param \Amasty\MultiInventory\Model\Dispatch                            $dispatch
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface                  $orderItemRepository
     * @param \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface $repository
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface      $itemRepository
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory                    $quoteOptionFactory
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor         $processor
     * @param \Amasty\MultiInventory\Model\EmailNotification                   $emailNotification
     * @param \Magento\Framework\File\Size                                     $fileSize
     * @param \Amasty\MultiInventory\Logger\Logger                             $logger
     * @param System                                                           $system
     * @param Context                                                          $context
     */
    public function __construct(
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Model\Dispatch $dispatch,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface $repository,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $quoteItemWhCollection,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor,
        \Amasty\MultiInventory\Model\EmailNotification $emailNotification,
        \Magento\Framework\File\Size $fileSize,
        \Amasty\MultiInventory\Logger\Logger $logger,
        System $system,
        Context $context
    ) {
        parent::__construct($context);
        $this->factory = $factory;
        $this->system = $system;
        $this->dispatch = $dispatch;
        $this->repository = $repository;
        $this->itemRepository = $itemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->processor = $processor;
        $this->emailNotification = $emailNotification;
        $this->fileSize = $fileSize;
        $this->logger = $logger;
        $this->quoteItemWhCollection = $quoteItemWhCollection;
    }

    /**
     * @return int
     */
    public function getDefaultId()
    {
        return $this->factory->create()->getDefaultId();
    }

    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function dispatchWarehouseForQuote($order)
    {
        $result = [];
        /** @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\Collection $quoteWhItems */
        $quoteWhItems = $this->quoteItemWhCollection->create()
            ->getWarehousesFromQuote($order->getQuoteId());

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId() || $this->isSimple($item)) {
                $warehouseQuote = $quoteWhItems->getItemByColumnValue('quote_item_id', $item->getQuoteItemId());
                if ($warehouseQuote !== null) {
                    $result[] = $this->getArrayItem($item, $warehouseQuote->getWarehouseId());
                }
            }
        }

        return $result;
    }

    /**
     * Call Dispatches from Config
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return array
     */
    public function calcDispatch($item)
    {
        $this->dispatch->setOrderItem($item);
        $this->dispatch->setDirection(\Amasty\MultiInventory\Model\Dispatch::DIRECTION_ORDER);
        $this->dispatch->searchWh();

        return $this->getArrayItem($item, $this->dispatch->getWarehouse());
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param int $warehouse
     * @return array
     */
    public function getArrayItem($item, $warehouse)
    {
        return [
            'order_id' => $item->getOrderId(),
            'order_item_id' => $item->getId(),
            'warehouse_id' => $warehouse,
            'product_id' => $item->getProductId(),
            'qty' => $item->getQtyOrdered()
        ];
    }

    /**
     * change Qty
     *
     * @param     $item
     * @param     $entity
     * @param int $ship
     * @param     $event
     */
    public function setShip(\Amasty\MultiInventory\Model\Warehouse\Order\Item $item, $entity, $ship = 0, $event)
    {
        $fields = $item->toArray();
        if (count($entity) > 0) {
            $fields['qty_ordered'] = $this->getQtyToShip($entity, $fields);
            $itemId = ($fields['parent_item_id']) ? $fields['parent_item_id'] : $fields['order_item_id'];
            if (isset($entity['warehouse'][$itemId])) {
                $warehouse = $this->getWarehouse($entity, $itemId);
                if ($warehouse != 0 && $warehouse != $fields['warehouse_id']) {
                    $this->changeWarehouse($fields, $warehouse, $ship);
                }
            } else {
                $warehouse = $fields['warehouse_id'];
            }
        } else {
            $warehouse = $fields['warehouse_id'];
        }
        if (!$ship) {
            $this->setQty($fields, $warehouse, $event);
        }
    }

    /**
     * Return Qty
     *
     * @param \Amasty\MultiInventory\Model\Warehouse\Order\Item $item
     * @param \Magento\Sales\Model\AbstractModel $entity
     */
    public function setReturn($item, $entity)
    {
        $fields = $item->toArray();
        $fields['qty_ordered'] = $this->getQtyToReturn($entity, $fields);
        $warehouse = $fields['warehouse_id'];
        $this->setReturnQty($fields, $warehouse);
    }

    /**
     * change manual Warehouse
     *
     * @param $fields
     * @param $warehouse
     * @param $ship
     */
    private function changeWarehouse($fields, $warehouse, $ship)
    {
        $orderItem = $this->repository->getById($fields['item_id']);
        $stockItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $fields['warehouse_id']);
        $oldQty = $stockItem->getQty();
        if (!$ship) {
            $stockItem->setShipQty($stockItem->getShipQty() - $fields['qty_ordered']);
        } else {
            $stockItem->setQty($stockItem->getQty() + $fields['qty_ordered']);
        }
        $stockItem->recalcAvailable();
        $this->itemRepository->save($stockItem);
        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                'change warehouse',
                'null',
                'true'
            );
        }
        $newItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $warehouse);
        $oldQty = $newItem->getQty();
        if (!$ship) {
            $newItem->setShipQty($newItem->getShipQty() + $fields['qty_ordered']);
        } else {
            $newItem->setQty($newItem->getQty() - $fields['qty_ordered']);
        }
        $newItem->recalcAvailable();
        $this->itemRepository->save($newItem);
        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $newItem->getProduct()->getSku(),
                $newItem->getProductId(),
                $newItem->getWarehouse()->getTitle(),
                $newItem->getWarehouse()->getCode(),
                $oldQty,
                $newItem->getQty(),
                'change warehouse',
                'null',
                'true'
            );
        }
        $orderItem->setWarehouseId($warehouse);
        $this->repository->save($orderItem);
        $this->processor->reindexRow($fields['product_id']);
    }

    /**
     * Set Qty
     *
     * @param array $fields
     * @param int $warehouseId
     */
    private function setQty($fields, $warehouseId, $event)
    {
        $stockItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $warehouseId);
        $oldQty = $stockItem->getQty();
        $stockItem->setQty($stockItem->getQty() - $fields['qty_ordered']);
        $stockItem->setShipQty($stockItem->getShipQty() - $fields['qty_ordered']);
        $stockItem->recalcAvailable();
        $this->itemRepository->save($stockItem);
        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                $event
            );
        }
        $this->processor->reindexRow($fields['product_id']);
        $this->checkLowStock($stockItem);
    }

    /**
     * @param array $fields
     * @param int $warehouseId
     */
    private function setReturnQty($fields, $warehouseId)
    {
        $stockItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $warehouseId);
        $oldQty = $stockItem->getQty();

        $orderItem = $this->orderItemRepository->get($fields['order_item_id']);

        if ($orderItem->getQtyOrdered() > $orderItem->getQtyShipped()) {
            $stockItem->setShipQty($stockItem->getShipQty() - $fields['qty_ordered']);
        } else {
            $stockItem->setQty($stockItem->getQty() + $fields['qty_ordered']);
        }

        $stockItem->recalcAvailable();
        $this->itemRepository->save($stockItem);
        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                'creditmemo'
            );
        }
        $this->processor->reindexRow($fields['product_id']);
    }

    /**
     * @param $entity
     * @param $field
     * @return int
     */
    private function getWarehouse($entity, $field)
    {
        foreach ($entity['warehouse'] as $key => $record) {
            if ($key == $field) {
                return $record;
            }
        }

        return 0;
    }

    /**
     * @param $entity
     * @param $field
     * @return mixed
     */
    private function getQtyToShip($entity, $field)
    {
        $itemId = $field['parent_item_id'] ?: $field['order_item_id'];

        foreach ($entity['items'] as $record) {
            if ($record->getOrderItemId() == $itemId) {
                return $record->getQty();
            }
        }

        return $field['qty_ordered'];
    }

    /**
     * @param $entity
     * @param $field
     * @return mixed
     */
    private function getQtyToReturn($entity, $field)
    {
        $itemId = $field['parent_item_id'] ?: $field['order_item_id'];

        foreach ($entity['items'] as $record) {
            if ($record->getOrderItemId() == $itemId) {
                return $record->getQty();
            }
        }

        return 0;
    }

    /**
     * check for Low warehouses
     *
     * @param \Amasty\MultiInventory\Model\Warehouse\Item $item
     */
    public function checkLowStock(\Amasty\MultiInventory\Model\Warehouse\Item $item)
    {
        $qty = $item->getAvailableQty();

        if ($qty <= $this->system->getLowStock()) {
            $this->emailNotification->sendLowStock($item->getProductId(), $item->getWarehouseId());
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function setOrderEmail($order)
    {
        $this->emailNotification->setNewOrder($order);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item | \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public function isSimple($item)
    {
        return $item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->fileSize->getFileSizeInMb($this->getMaxSizeFile());
        if ($maxImageSize) {
            $message = __('Make sure your file isn\'t more than %1 MB.', $maxImageSize);
        } else {
            $message = __('We can\'t provide the upload settings right now.');
        }
        return $message;
    }

    /**
     * @return float
     */
    public function getMaxSizeFile()
    {
        return $this->fileSize->getMaxFileSize();
    }
}
