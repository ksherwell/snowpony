<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Lowstock;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Lowstock\CollectionFactory
     */
    private $lowstocksFactory;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var string
     */
    protected $_template = 'Amasty_MultiInventory::grid.phtml';

    /**
     * @var array
     */
    private $warehouses;

    /**
     * @var array
     */
    protected $_defaultFilter = ['report_warehouse' => ''];

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Lowstock\CollectionFactory $lowstocksFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Lowstock\CollectionFactory $lowstocksFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        array $data = []
    ) {
        $this->lowstocksFactory = $lowstocksFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->_defaultFilter['report_warehouse'] = [$this->warehouseFactory->create()->getDefaultId()];
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->lowstocksFactory->create()->addAttributeToSelect('*')
            ->joinInventoryItem('qty')
            ->setSimpleType()
            ->useNotifyStockQtyFilter()
            ->setOrder(
                'qty',
                \Magento\Framework\Data\Collection::SORT_ORDER_ASC
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return array
     */
    public function getWarehouses()
    {
        if ($this->warehouses == null) {
            $collection = $this->warehouseFactory->create()
                ->getCollection()
                ->clear()
                ->addFieldToSelect('warehouse_id')
                ->addFieldToSelect('title')
                ->toArray();

            foreach ($collection['items'] as $item) {
                $this->warehouses[$item['warehouse_id']] = $item['title'];
            }
        }

        return $this->warehouses;
    }

    /**
     * @return string
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        parent::_prepareFilterButtons();
        $this->addChild(
            'refresh_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Refresh'), 'onclick' => "{$this->getJsObjectName()}.doFilter();", 'class' => 'task']
        );
    }

    /**
     * @param mixed $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        $warehouse = (isset($data['report_warehouse']) ? $data['report_warehouse'] : $this->warehouseFactory->create()->getDefaultId());
            $this->getCollection()->setWarehouses($warehouse);
            $this->setFilter('report_warehouse', [$warehouse]);

        return parent::_setFilterValues($data);
    }

    /**
     * Set filter
     *
     * @param string $name
     * @param string $value
     * @return void
     * @codeCoverageIgnore
     */
    public function setFilter($name, $value)
    {
        if ($name) {
            $this->filters[$name] = $value;
        }
    }

    /**
     * Get filter by key
     *
     * @param string $name
     * @return string
     */
    public function getFilter($name)
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        } else {
            return $this->getRequest()->getParam($name) ? htmlspecialchars($this->getRequest()->getParam($name)) : '';
        }
    }
}
