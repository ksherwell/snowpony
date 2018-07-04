<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    const COLUMN_WAREHOUSE = 'Amasty\MultiInventory\Ui\Component\Listing\Column\Stock\Warehouse';
    const COLUMN_WAREHOUSE_SHIP = 'Amasty\MultiInventory\Ui\Component\Listing\Column\Stock\WarehouseShip';
    const COLUMN_WAREHOUSE_AVAILABLE = 'Amasty\MultiInventory\Ui\Component\Listing\Column\Stock\WarehouseAvailable';
    const COLUMN_WAREHOUSE_SHELF = 'Amasty\MultiInventory\Ui\Component\Listing\Column\Stock\WarehouseShelf';

    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\MultiInventory\Ui\Component\ColumnFactory
     */
    private $columnFactory;

    /**
     * Columns constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Amasty\MultiInventory\Ui\Component\ColumnFactory $columnFactory
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Amasty\MultiInventory\Ui\Component\ColumnFactory $columnFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     *  Prepare fields
     */
    public function prepare()
    {
        $classes = [
            [
                'code' => '',
                'class' => self::COLUMN_WAREHOUSE,
                'title' => 'Qty',
                'filter' => 'textRange',
                'editor' => 1,
                'validation' => 1,
            ]
        ];
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $collection = $this->collectionFactory->create()->addFieldToFilter('manage', 1)->setOrder('warehouse_id');
        foreach ($collection as $warehouse) {
            $config = [];
            if (!isset($this->components[$warehouse->getCode()])) {
                foreach ($classes as $class) {
                    if ($warehouse->getIsGeneral() && $class['code'] == 'shelf') {
                        continue;
                    }
                    if ($class['code']) {
                        $class['code'] = $warehouse->getCode() . "_" . $class['code'];
                    } else {
                        $class['code'] = $warehouse->getCode();
                    }
                    $class['title'] = $warehouse->getTitle();
                    $class['is_general'] = $warehouse->getIsGeneral();
                    $config['sortOrder'] = $warehouse->getId() * $columnSortOrder;
                    $column = $this->columnFactory->create($class, $this->getContext(), $config);
                    $column->prepare();
                    $this->addComponent($class['code'], $column);
                    $columnSortOrder++;
                }
            }
        }
        parent::prepare();
    }
}
