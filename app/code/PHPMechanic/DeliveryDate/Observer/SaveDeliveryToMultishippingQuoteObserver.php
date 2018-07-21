<?php

namespace PHPMechanic\DeliveryDate\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveDeliveryToMultishippingQuoteObserver implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$deiveryDate = $observer->getEvent()->getRequest()->getPost('delivery_date');
    	$authorityTo = $observer->getEvent()->getRequest()->getPost('authority_to');
    	$deiveryComment = $observer->getEvent()->getRequest()->getPost('delivery_comment');
 		$addresses  = $observer->getEvent()->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            $address->setDeliveryDate($deiveryDate[$address->getId()]);
            $address->setAuthorityTo($authorityTo[$address->getId()]);
            $address->setDeliveryComment($deiveryComment[$address->getId()]);
        }
        return $this;
    }
}