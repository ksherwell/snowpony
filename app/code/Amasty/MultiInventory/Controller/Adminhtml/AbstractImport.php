<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Amasty\MultiInventory\Ui\Component\MassAction\Filter;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import\CollectionFactory;

class AbstractImport extends \Magento\Backend\App\Action
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
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface
     */
    private $importRepository;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $processor;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ItemFactory
     */
    private $factory;

    /**
     * @var \Amasty\MultiInventory\Model\Publish\Csv
     */
    public $csv;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    public $timezone;

    /**
     * @var \Amasty\MultiInventory\Logger\Logger
     */
    private $logger;
    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Full
     */
    private $fullReindexAction;

    /**
     * AbstractImport constructor.
     *
     * @param Context                                                       $context
     * @param Filter                                                        $filter
     * @param CollectionFactory                                             $collectionFactory
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface   $repository
     * @param \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface $importRepository
     * @param \Amasty\MultiInventory\Model\Warehouse\ItemFactory            $factory
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor      $processor
     * @param \Amasty\MultiInventory\Model\Publish\Csv                      $csv
     * @param \Magento\Framework\Stdlib\DateTime\Timezone                   $timezone
     * @param \Amasty\MultiInventory\Logger\Logger                          $logger
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Full    $fullReindexAction
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $repository,
        \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface $importRepository,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor,
        \Amasty\MultiInventory\Model\Publish\Csv $csv,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Amasty\MultiInventory\Logger\Logger $logger,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Full $fullReindexAction
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->processor = $processor;
        $this->factory = $factory;
        $this->csv = $csv;
        $this->timezone = $timezone;
        $this->importRepository = $importRepository;
        $this->logger = $logger;
        $this->fullReindexAction = $fullReindexAction;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $this->import();
    }

    /**
     * Import
     */
    public function import()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $stock) {
            try {
                $item = $this->repository->getByProductWarehouse($stock->getProductId(), $stock->getWarehouseId());
                $oldQty = $item->getQty();
                $item->setProductId($stock->getProductId());
                $item->setWarehouseId($stock->getWarehouseId());
                $item->setQty($stock->getNewQty());
                $item->recalcAvailable();
                $this->repository->save($item);
                $this->logger->infoWh(
                    $item->getProduct()->getSku(),
                    $item->getProductId(),
                    $item->getWarehouse()->getTitle(),
                    $item->getWarehouse()->getCode(),
                    $oldQty,
                    $item->getQty(),
                    'null',
                    'Import'
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->fullReindexAction->setUpdateStockStatus(true);
        $this->processor->reindexAll();
        $this->remove();
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been imported.', $collectionSize)
        );
    }

    /**
     * Remove
     */
    private function remove()
    {
        $importNumber = $this->filter->getImportNumber();

        $collection = $this->collectionFactory->create()->addFieldToFilter('import_number', $importNumber);
        foreach ($collection as $item) {
            $this->importRepository->delete($item);
        }
    }
}
