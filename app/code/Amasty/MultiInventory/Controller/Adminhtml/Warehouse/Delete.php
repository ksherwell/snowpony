<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Magento\Backend\App\Action;

class Delete extends \Amasty\MultiInventory\Controller\Adminhtml\Warehouse
{
    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
     */
    public function __construct(
        Action\Context $context,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->processor = $processor;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('warehouse_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->repository->getById($id);
                if (!$model->getIsGeneral()) {
                    $this->repository->deleteById($id);
                    $this->processor->reindexAll();
                    $this->messageManager->addSuccessMessage(__('You deleted the warehouse.'));
                } else {
                    $this->messageManager->addErrorMessage(__('We can\'t delete a warehouse'));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['warehouse_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a warehouse to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
