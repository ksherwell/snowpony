<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse;

use Amasty\MultiInventory\Model\WarehouseFactory;

class AssignWarehouses extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'warehouse/edit/assign_warehouses.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Warehouse
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $factory;

    /**
     * @var WarehouseFactory
     */
    private $wh;

    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    public $helper;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * AssignWarehouses constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $factory
     * @param WarehouseFactory $wh
     * @param \Amasty\MultiInventory\Helper\Data $helper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Amasty\MultiInventory\Helper\System $system
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $factory,
        \Amasty\MultiInventory\Model\WarehouseFactory $wh,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\MultiInventory\Helper\System $system,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->factory = $factory;
        $this->wh = $wh;
        $this->helper = $helper;
        $this->system = $system;
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Warehouse',
                'warehouse.grid'
            );
        }

        return $this->blockGrid;

    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $list = $this->wh->create()->getWhNotActive();
        $warehouses = $this->factory->create()
            ->addFieldToFilter('product_id', $this->getProduct()->getId())
            ->addFieldToSelect(['warehouse_id', 'qty', 'room_shelf', 'backorders', 'stock_status']);
        if (count($list) > 0) {
            $warehouses->addFieldToFilter('warehouse_id', ['nin' => $list]);
        }
        $data = [];
        if (!empty($warehouses)) {
            /** @var \Amasty\MultiInventory\Model\Warehouse\Item $item */
            foreach ($warehouses as $item) {
                if (!$item->getWarehouse()->getIsGeneral()) {
                    $data[$item->getWarehouseId()] = $item->getData();
                }
            }

            return $this->jsonEncoder->encode($data);
        }

        return '{}';
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return mixed
     */
    public function isAvailable()
    {
        return $this->system->getAvailableDecreese();
    }
}
