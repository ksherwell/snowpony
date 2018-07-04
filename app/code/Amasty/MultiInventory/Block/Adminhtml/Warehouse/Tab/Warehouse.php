<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Warehouse extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ItemFactory
     */
    private $warehouseItemFactory;

    /**
     * @var \Amasty\MultiInventory\Model\Config\Source\Stock
     */
    private $stockOptions;

    /**
     * @var \Amasty\MultiInventory\Model\Config\Source\Backorders
     */
    private $backordersOptions;

    /**
     * Warehouse constructor.
     *
     * @param \Magento\Backend\Block\Template\Context               $context
     * @param \Magento\Backend\Helper\Data                          $backendHelper
     * @param \Amasty\MultiInventory\Model\WarehouseFactory         $warehouseFactory
     * @param \Amasty\MultiInventory\Model\Warehouse\ItemFactory    $warehouseItemFactory
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Amasty\MultiInventory\Model\Config\Source\Stock      $stockOptions
     * @param \Amasty\MultiInventory\Model\Config\Source\Backorders $backordersOptions
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\MultiInventory\Model\Config\Source\Stock $stockOptions,
        \Amasty\MultiInventory\Model\Config\Source\Backorders $backordersOptions,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->warehouseFactory = $warehouseFactory;
        $this->coreRegistry = $coreRegistry;
        $this->warehouseItemFactory = $warehouseItemFactory;
        $this->stockOptions = $stockOptions;
        $this->backordersOptions = $backordersOptions;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amasty_multi_inventory_warehouse_grid');
        $this->setDefaultSort('warehouse_id');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
    }

    /**
     * @return array|null
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        /** @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection $collection */
        $collection = $this->warehouseFactory->create()
            ->getCollection()
            ->addFieldToFilter('manage', 1);
        $collection->addProduct($this->getProduct()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'renderer' => 'Amasty\MultiInventory\Block\Widget\Grid\Column\Renderer\Longtext',
                'filter' => false
            ]
        );
        $this->addColumn(
            'in_warehouse',
            [
                'header' => __('Manage Product Stock'),
                'type' => 'checkbox',
                'name' => 'in_warehouse',
                'values' => $this->getSelectedWarehouses(),
                'disabled_values' => $this->getDisabledValuesWarehouses(),
                'index' => 'warehouse_id',
                'filter' => false,
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'available_qty',
            [
                'header' => __('Available Qty'),
                'type' => 'number',
                'index' => 'available_qty',
                'filter' => false,
            ]
        );
        $this->addColumn(
            'ship_qty',
            [
                'header' => __('Qty To Ship'),
                'type' => 'number',
                'index' => 'ship_qty',
                'filter' => false
            ]
        );

        $this->addColumn(
            'qty',
            [
                'header' => __('Total Qty'),
                'type' => 'number',
                'index' => 'qty',
                'editable' => true,
                'filter' => false
            ]
        );
        $this->addColumn(
            'room_shelf',
            [
                'header' => __('Room & Shelf'),
                'type' => 'string',
                'index' => 'room_shelf',
                'editable' => true,
                'filter' => false
            ]
        );
        $this->addColumn(
            'stock_status',
            [
                'header' => __('Stock Status'),
                'type' => 'select',
                'index' => 'stock_status',
                'options' =>  $this->stockOptions->toFlatArray(),
                'editable' => true,
                'filter' => false
            ]
        );
        $this->addColumn(
            'backorders',
            [
                'header' => __('Backorders'),
                'type' => 'select',
                'index' => 'backorders',
                'options' =>  $this->backordersOptions->getGridOptions(),
                'editable' => true,
                'filter' => false
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('amasty_multi_inventory/warehouse/gridwh', ['product_id' => $this->getProduct()->getId()]);
    }

    /**
     * @return array
     */
    private function getSelectedWarehouses()
    {
        $warehouses = $this->getRequest()->getPost('selected_warehouses');
        if ($warehouses === null) {
            $collection = $this->warehouseItemFactory->create()->getCollection()
                ->addFieldToFilter('product_id', $this->getProduct()->getId());
            foreach ($collection as $item) {
                $warehouses[] = $item->getWarehouseId();
            }
        }

        return $warehouses;
    }

    /**
     * Get Default Id
     *
     * @return array
     */
    private function getDisabledValuesWarehouses()
    {
        return [
            $this->warehouseFactory->create()->getDefaultId()
        ];
    }
}
