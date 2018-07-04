<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer\Sales\Order;

use Magento\Framework\Event\Observer as EventObserver;

class CreateCreditmemoObserver extends CreateAbstractObserver
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * CreateCreditmemoObserver constructor.
     *
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory $collectionFactory
     * @param \Amasty\MultiInventory\Helper\Data                                                $helper
     * @param \Amasty\MultiInventory\Helper\System                                              $system
     * @param \Magento\Framework\Message\ManagerInterface                                       $messageManager
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory $collectionFactory,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Amasty\MultiInventory\Helper\System $system,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($collectionFactory, $helper, $system);
        $this->messageManager = $messageManager;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->isCanExecute()) {
            parent::execute($observer);
        } else {
            $this->messageManager->addNoticeMessage(
                __('The "Return Credit Memo Item to Stock" setting is disabled. '
                    . 'The returned item(s) do not affect the stock.')
            );
        }
    }

    /**
     * @param \Amasty\MultiInventory\Model\Warehouse\Order\Item $item
     * @param \Magento\Sales\Model\Order\Creditmemo $entity
     */
    protected function processItem($item, $entity)
    {
        $this->helper->setReturn($item, $entity);
        $this->messageManager->addNoticeMessage(
            __(
                'The returned item(s) affected the product quantity in the appropriate Warehouse %1.',
                $item->getWarehouse()->getTitle()
            )
        );
    }

    /**
     * @return bool
     */
    protected function isCanExecute()
    {
        return parent::isCanExecute() && $this->system->getReturnCreditmemo();
    }
}
