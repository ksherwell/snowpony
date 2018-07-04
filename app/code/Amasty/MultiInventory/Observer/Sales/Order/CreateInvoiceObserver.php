<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer\Sales\Order;

use Amasty\MultiInventory\Helper\System as HelperSystem;
use Magento\Framework\Event\Observer as EventObserver;

class CreateInvoiceObserver extends CreateAbstractObserver
{
    /**
     * @return bool
     */
    protected function isCanExecute()
    {
        return parent::isCanExecute() && $this->system->getPhysicalDecreese() == HelperSystem::ORDER_INVOICED;
    }

    /**
     * @return string
     */
    protected function getEventName()
    {
        return 'invoice';
    }
}
