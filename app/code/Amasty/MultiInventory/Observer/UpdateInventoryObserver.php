<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class UpdateInventoryObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    private $stockProcessor;


    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockProcessor
    ) {
        $this->stockProcessor = $stockProcessor;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getEvent()->getObject();
        $productIds = $object->getRegisteredEntity(Warehouse::CACHE_TAG);
        if (count($productIds)) {
            $this->stockProcessor->reindexList($productIds);
        } else {
            $this->stockProcessor->reindexAll();
        }

        return $this;
    }
}
