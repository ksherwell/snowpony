<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer\Sales\Order;

use Amasty\MultiInventory\Helper\System as HelperSystem;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

abstract class CreateAbstractObserver implements ObserverInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    protected $helper;

    /**
     * @var HelperSystem
     */
    protected $system;

    /**
     * CreateAbstractObserver constructor.
     *
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory $collectionFactory
     * @param \Amasty\MultiInventory\Helper\Data                                                $helper
     * @param HelperSystem                                                                      $system
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory $collectionFactory,
        \Amasty\MultiInventory\Helper\Data $helper,
        HelperSystem $system
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->system = $system;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->isCanExecute()) {
            /** @var \Magento\Sales\Model\AbstractModel $entity */
            $entity = $observer->getEvent()->getDataObject();
            if (!is_object($entity) || !$entity->getOrderId()) {
                return;
            }

            $collection = $this->collectionFactory->create()->getOrderItemInfo($entity->getOrderId());
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $this->processItem($item, $entity);
                }
            }
        }
    }

    /**
     * @param \Amasty\MultiInventory\Model\Warehouse\Order\Item $item
     * @param \Magento\Sales\Model\AbstractModel $entity
     */
    protected function processItem($item, $entity)
    {
        $this->helper->setShip($item, $entity, $this->isShip(), $this->getEventName());
    }

    /**
     * @return bool
     */
    protected function isCanExecute()
    {
        return $this->system->isMultiEnabled();
    }

    /**
     * @return bool
     */
    protected function isShip()
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getEventName()
    {
        return '';
    }
}
