<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer\Sales\Order;

use Amasty\MultiInventory\Helper\System as HelperSystem;
use Magento\Framework\Event\Observer as EventObserver;

class CreateShipmentObserver extends CreateAbstractObserver
{
    protected function isShip()
    {
        return !($this->system->getPhysicalDecreese() == HelperSystem::ORDER_SHIPMENT);
    }

    protected function getEventName()
    {
        return 'shipment';
    }
}
