<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->processor = $processor;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $warehouse) {
            if (!$warehouse->getIsgeneral()) {
                $this->repository->deleteById($warehouse->getId());
            } else {
                $collectionSize--;
            }
        }
        $this->processor->reindexAll();
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
