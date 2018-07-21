<?php

namespace PHPMechanic\DeliveryDate\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveDeliveryToMultishippingOrderObserver implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)

    {
        $observer->getEvent()->getOrder()->setDeliveryDate($observer->getEvent()->getAddress()->getDeliveryDate());
        $observer->getEvent()->getOrder()->setAuthorityTo($observer->getEvent()->getAddress()->getAuthorityTo());
        $observer->getEvent()->getOrder()->setDeliveryComment($observer->getEvent()->getAddress()->getDeliveryComment());

        return $this;
    }
}