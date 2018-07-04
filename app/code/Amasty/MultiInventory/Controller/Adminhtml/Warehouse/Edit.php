<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Amasty\MultiInventory\Controller\Adminhtml\Warehouse
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->warehouseFactory = $warehouseFactory;
        $this->repository = $repository;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('warehouse_id');
        if ($id) {
            try {
                $model = $this->repository->getById($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This warehouse no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->warehouseFactory->create();
        }
        if ($model->getStockId() == \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID) {
            $this->messageManager->addErrorMessage(__('You can\'t edit the warehouse.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $this->coreRegistry->register('amasty_multi_inventory_warehouse', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_MultiInventory::warehouses');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Warehouse') : __('New Warehouse'),
            $id ? __('Edit Warehouse') : __('New Warehouse')
        );
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Warehouse'));

        return $resultPage;
    }
}
