<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item;

use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Amasty\MultiInventory\Model\Warehouse\Order\Item',
            'Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item'
        );
    }

    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param WarehouseFactory $factory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->factory = $factory;
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function getDataOrder($orderId)
    {
        $this->addFieldToFilter('main_table.order_id', $orderId);
        if ($this->getSize()) {
            $this->getSelect()
                ->joinLeft(
                    ['soi' => $this->getTable('sales_order_item')],
                    'soi.item_id = main_table.order_item_id',
                    ['product' => 'soi.product_id', 'item' => 'soi.item_id', 'parent' => 'soi.parent_item_id']
                )->joinLeft(
                    ['whp' => $this->getTable('amasty_multiinventory_warehouse_item')],
                    'whp.warehouse_id = main_table.warehouse_id AND whp.product_id = soi.product_id'
                )
            ->where('whp.warehouse_id <> ?', $this->factory->create()->getDefaultId());
        }

        return $this;
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function getOrderItemInfo($orderId)
    {
        $this->addFieldToFilter('main_table.order_id', $orderId);
        $this->getSelect()->joinLeft(
            ['soi' => $this->getTable('sales_order_item')],
            'soi.item_id = main_table.order_item_id',
            ['parent_item_id', 'product_id', 'qty_ordered']
        );

        return $this;
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function getWarehousesFromOrder($orderId)
    {
        $this->addFieldToFilter('order_id', $orderId);

        return $this;
    }
}
