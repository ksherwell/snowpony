<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

class Grid extends \Amasty\MultiInventory\Controller\Adminhtml\Warehouse
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * Grid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * Get Grid
     *
     * @return $this
     */
    public function execute()
    {
        $warehouseId = (int)$this->getRequest()->getParam('warehouse_id', false);
        if ($warehouseId) {
            $warehouse = $this->repository->getById($warehouseId);
        } else {
            $warehouse = $this->warehouseFactory->create();
        }
        $this->registry->register('amasty_multi_inventory_warehouse', $warehouse);
        if (!$warehouse) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('warehouse/*/', ['_current' => true, 'warehouse_id' => null]);
        }
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                'Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Product',
                'warehouse.product.grid'
            )->toHtml()
        );
    }
}
