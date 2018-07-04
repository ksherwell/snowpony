<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Catalog inventory module observer
 */
class CancelOrderItemObserver implements ObserverInterface
{
    /**
     * @var StockManagementInterface
     */
    private $helper;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\OrderItemRepository
     */
    private $orderItemRepository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    protected $system;

    /**
     * @var \Amasty\MultiInventory\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    protected $processor;

    /**
     * CancelOrderItemObserver constructor.
     *
     * @param \Amasty\MultiInventory\Helper\Data                               $helper
     * @param \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface $orderItemRepository
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface      $stockRepository
     * @param \Amasty\MultiInventory\Helper\System                             $system
     * @param \Amasty\MultiInventory\Logger\Logger                             $logger
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor         $processor
     */
    public function __construct(
        \Amasty\MultiInventory\Helper\Data $helper,
        \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface $orderItemRepository,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $stockRepository,
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Logger\Logger $logger,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
    ) {
        $this->helper              = $helper;
        $this->orderItemRepository = $orderItemRepository;
        $this->stockRepository     = $stockRepository;
        $this->system              = $system;
        $this->logger              = $logger;
        $this->processor           = $processor;
    }

    /**
     * Cancel order item
     *
     * @param   EventObserver $observer
     * @return  void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->system->isMultiEnabled()) {
            return;
        }
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && $item->getProductId() && empty($children) && $qty > 0) {
            $warehouseId = $this->orderItemRepository->getByOrderItemId($item->getId())->getWarehouseId();
            $productId   = $item->getProductId();
            $stockItem   = $this->stockRepository->getByProductWarehouse($productId, $warehouseId);
            if (!$stockItem->getId()) {
                return;
            }

            $oldQty = $stockItem->getQty();
            $stockItem->setShipQty($stockItem->getShipQty() - $qty);
            $stockItem->recalcAvailable();
            if ($stockItem->getAvailableQty() > 0) {
                $stockItem->setStockStatus(\Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK);
            }
            $this->stockRepository->save($stockItem);

            if ($this->system->isEnableLog()) {
                $this->logger->infoWh(
                    $stockItem->getProduct()->getSku(),
                    $stockItem->getProductId(),
                    $stockItem->getWarehouse()->getTitle(),
                    $stockItem->getWarehouse()->getCode(),
                    $oldQty,
                    $stockItem->getQty(),
                    'cancel'
                );
            }
            $this->processor->reindexRow($productId);
        }
    }
}
