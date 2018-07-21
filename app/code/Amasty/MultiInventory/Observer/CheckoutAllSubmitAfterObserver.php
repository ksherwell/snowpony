<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface;
use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class CheckoutAllSubmitAfterObserver implements ObserverInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var WarehouseOrderItemRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    protected $stockCollection;

    /**
     * @var Warehouse\Order\Processor
     */
    private $orderProcessor;

    /**
     * CheckoutAllSubmitAfterObserver constructor.
     * @param Warehouse\Order\ItemFactory $orderItemFactory
     * @param WarehouseOrderItemRepositoryInterface $repository
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository
     * @param \Amasty\MultiInventory\Helper\Data $helper
     * @param \Amasty\MultiInventory\Helper\System $system
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
     */
    public function __construct(
        \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory $orderItemFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $stockCollection,
        \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface $repository,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Amasty\MultiInventory\Helper\System $system,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor,
        OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Amasty\MultiInventory\Model\Warehouse\Order\Processor $orderProcessor
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->stockCollection  = $stockCollection;
        $this->helper           = $helper;
        $this->system           = $system;
        $this->repository       = $repository;
        $this->processor        = $processor;
        $this->itemRepository   = $itemRepository;
        $this->messageManager   = $messageManager;
        $this->orderSender      = $orderSender;
        $this->checkoutSession  = $checkoutSession;
        $this->registry         = $registry;
        $this->orderProcessor   = $orderProcessor;
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

        $result = [];

        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $quote->setInventoryProcessed(true);
        $orders = [$order];
        try {
            if ($this->system->getDefinationWarehouse()) {
                $result = $this->helper->dispatchWarehouseForQuote($order);
            } else {
                $result = $this->orderProcessor->dispatchWarehouse($order);
            }
            list($result, $orders) = $this->orderProcessor->separateOrders($result, $order);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the order for warehouse now.'));
        }
        foreach ($result as $itemWrapper) {
            $this->convertToOrderItem($itemWrapper);
            $this->modifyWarehouseStock($itemWrapper);
        }
        if ($this->system->getPhysicalDecreese() == \Amasty\MultiInventory\Helper\System::ORDER_CREATION
            && $this->system->getAvailableDecreese()
        ) {
            if ($order->getId()) {
                $entity = [];
                $collection = $this->orderItemFactory->create()->getCollection()->getOrderItemInfo($order->getId());
                if ($collection->getSize()) {
                    foreach ($collection as $item) {
                        $this->helper->setShip($item, $entity, 0, 'order');
                    }
                }
            }
        }

        $orderIds = [];
        /** @var \Magento\Sales\Model\Order| $order */
        foreach ($orders as $order) {
            $orderIds[] = $order->getIncrementId();
            $order->setItems(null);
            if ($this->isCanSendEmail($quote)) {
                $this->orderSender->send($order);
            }

            $this->helper->setOrderEmail($order);
        }

        if (count($orderIds) > 1) {
            $this->checkoutSession->setSeparetedOrderIds($orderIds);
        }

        return $this;
    }

    /**
     * @param array $item
     *
     * @return $this
     */
    private function convertToOrderItem($item)
    {
        $orderItem = $this->orderItemFactory->create();
        unset($item['qty']);
        unset($item['product_id']);
        $orderItem->setData($item);
        $this->repository->save($orderItem);
        return $this;
    }

    /**
     * @param array $itemWrapper
     *
     * @return \Amasty\MultiInventory\Model\Warehouse\Item
     */
    private function modifyWarehouseStock($itemWrapper)
    {
        $productId = $itemWrapper['product_id'];
        /** @var \Amasty\MultiInventory\Model\Warehouse\Item $warehouseStock */
        $warehouseStock = $this->itemRepository->getByProductWarehouse($productId, $itemWrapper['warehouse_id']);
        $warehouseStock->setShipQty($warehouseStock->getShipQty() + $itemWrapper['qty']);
        $warehouseStock->recalcAvailable();
        $this->itemRepository->save($warehouseStock);
        $this->processor->reindexRow($productId);
        if ($this->system->getPhysicalDecreese() != \Amasty\MultiInventory\Helper\System::ORDER_CREATION) {
            $this->helper->checkLowStock($warehouseStock);
        }

        return $warehouseStock;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    private function isCanSendEmail($quote)
    {
        return !(bool)$quote->getPayment()->getOrderPlaceRedirectUrl()
        && !$this->registry->registry('multiinventory_cant_send_new_email');
    }
}
