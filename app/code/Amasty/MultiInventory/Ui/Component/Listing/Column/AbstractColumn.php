<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AbstractColumn extends Column
{

    const AMASTY_INVENTORY_ITEM = 'amasty_multiinventory_warehouse_item';

    const AMASTY_INVENTORY = 'amasty_multiinventory_warehouse';

    const AMASTY_INVENTORY_ORDER = 'amasty_multiinventory_warehouse_order_item';

    const CATALOG_INVENTORY = 'cataloginventory_stock_item';
    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    public $factory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    public $repository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    public $jsonEncoder;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    public $helper;
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item
     */
    private $warehouseStockResource;

    /**
     * AbstractColumn constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $factory
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Amasty\MultiInventory\Helper\System $helper
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $warehouseStockResource
     * @param \Amasty\MultiInventory\Model\Config\Source\Stock $stockOptions
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\MultiInventory\Helper\System $helper,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $warehouseStockResource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->factory = $factory;
        $this->repository = $repository;
        $this->resource = $resource;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->warehouseStockResource = $warehouseStockResource;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * @param int $id
     * @param string $field
     * @return string
     */
    public function getTotalQty($id, $field = 'qty')
    {
        return $this->warehouseStockResource->getTotalQty($id, $field);
    }

    /**
     * @param $id
     * @return array
     */
    public function getTotalSku($id)
    {
        return $this->warehouseStockResource->getTotalSku($id);
    }

    /**
     * @param $productId
     * @return array
     */
    public function getWarehouses($productId)
    {
        $resource = $this->factory->create()->getResource();
        $select = $this->getConnection()->select()->from(
            ['wi' => $resource->getTable(self::AMASTY_INVENTORY_ITEM)],
            ['warehouse_id', 'qty']
        );
        $select->joinLeft(
            ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
            'w.warehouse_id = wi.warehouse_id',
            ['title']
        );
        $select->where(
            'wi.product_id = :product_id and wi.warehouse_id <> :warehouse_id and wi.qty > 0'
        );

        $bind = [
            'warehouse_id' => (int)$this->factory->create()->getDefaultId(),
            'product_id' => (int)$productId
        ];

        return $this->getConnection()->fetchAssoc($select, $bind);
    }

    /**
     * @param $productId
     * @return array
     */
    public function getInventory($productId)
    {
        $resource = $this->factory->create()->getResource();
        $select = $this->getConnection()->select()->from(
            ['wi' => $resource->getTable(self::CATALOG_INVENTORY)],
            ['qty']
        );
        $select->joinLeft(
            ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
            'w.stock_id = wi.stock_id',
            ['title']
        );
        $select->where(
            'wi.product_id = :product_id'
        );

        $bind = [
            'product_id' => (int)$productId
        ];

        return $this->getConnection()->fetchAssoc($select, $bind);
    }

    /**
     * @param int      $productId
     * @param int|null $warehouseId
     *
     * @return array
     */
    public function getProductStockData($productId, $warehouseId = null)
    {
        return $this->warehouseStockResource->getProductStockData($productId, $warehouseId);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getWarehousesOrder($orderId)
    {
        $resource = $this->factory->create()->getResource();
        $select = $this->getConnection()->select()->from(
            ['wi' => $resource->getTable(self::AMASTY_INVENTORY_ORDER)],
            []
        );
        $select->joinLeft(
            ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
            'w.warehouse_id = wi.warehouse_id',
            ['title']
        );
        $select->where(
            'wi.order_id = :order_id'
        );

        $bind = [
            'order_id' => (int)$orderId
        ];
        $result = $this->getConnection()->fetchCol($select, $bind);

        if (!count($result)) {
            $select = $this->getConnection()->select()->from(
                ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
                ['title']
            );
            $select->where(
                'warehouse_id = :warehouse_id'
            );
            $bind = ['warehouse_id' => (int)$this->factory->create()->getDefaultId()];
            $result = [$this->getConnection()->fetchOne($select, $bind)];
        }

        return $result;
    }
}
