<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Order\Email\Items\Order;

use Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Item as OrderItem;

class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ItemFactory
     */
    private $factory;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $whFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var ItemFactory
     */
    private $orderFactory;

    /**
     * DefaultOrder constructor.
     * @param Template\Context $context
     * @param \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $whFactory
     * @param ItemFactory $orderFactory
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory,
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory $orderFactory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->factory = $factory;
        $this->whFactory = $whFactory;
        $this->repository = $repository;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param OrderItem $item
     * @return string
     */
    public function getItemWarehouse(OrderItem $item)
    {
        $collection = $this->getCollectionWh($item);
        if ($collection->getSize()) {
            $text = '';
            foreach ($collection as $orderItem) {
                $text .= $orderItem->getWarehouse()->getTitle() . "<br/>";
            }
            return $text;
        }

        return $this->repository->getById($this->whFactory->create()->getDefaultId())->getTitle();
    }

    /**
     * @param OrderItem $item
     * @return string
     */
    public function getItemRoomShelf(OrderItem $item)
    {
        $collection = $this->getCollectionWh($item);
        $text = '';
        $products = [];
        if ($collection->getSize()) {
            foreach ($collection as $orderItem) {
                if ($item->getHasChildren()) {
                    foreach ($item->getChildrenItems() as $child) {
                        if ($orderItem->getOrderItemId() == $child->getId()) {
                            $products[] = $child->getProductId();
                        }
                    }
                } else {
                    $products[] = $item->getProductId();
                }
                $warehouse = $orderItem->getWarehouseId();
                $records = $this->factory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['in' => $products])
                    ->addFieldToFilter('warehouse_id', $warehouse);
                foreach ($records as $record) {
                    $text .= $record->getRoomShelf() . "<br/>";
                }
            }
        }

        return $text;
    }

    /**
     * @param OrderItem $item
     * @return $this
     */
    private function getCollectionWh(OrderItem $item)
    {
        $orderId = $this->getOrder()->getId();
        $itemId = $item->getId();
        $childs = [];
        if ($item->getHasChildren()) {
            foreach ($item->getChildrenItems() as $child) {
                $childs[] = $child->getId();
            }
        }
        if (!count($childs)) {
            $childs[] = $itemId;
        }
        return $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('order_item_id', ['in' => $childs])
            ->addFieldToFilter('order_id', $orderId);
    }
}
