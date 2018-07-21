<?php
namespace PHPMechanic\DeliveryDate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddHtmlToOrderShippingBlockObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function execute(EventObserver $observer)
    {
        if($observer->getElementName() == 'sales.order.info') {
            $orderShippingViewBlock = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingViewBlock->getOrder();
            $localeDate = $this->objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            if($order->getDeliveryDate() != '0000-00-00 00:00:00') {
                // $formattedDate = $localeDate->formatDateTime(
                //     $order->getDeliveryDate(),
                //     \IntlDateFormatter::MEDIUM,
                //     \IntlDateFormatter::MEDIUM,
                //     null,
                //     $localeDate->getConfigTimezone(
                //         \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                //         $order->getStore()->getCode()
                //     )
                // );
                $formattedDate = date('d-m-Y',strtotime($order->getDeliveryDate()));
            } else {
                $formattedDate = __('N/A');
            }

            $deliveryDateBlock = $this->objectManager->create('Magento\Framework\View\Element\Template');
            $deliveryDateBlock->setDeliveryDate($formattedDate);
            $authorityTo = ($order->getAuthorityTo()) ? 'yes' : 'no';
            $deliveryDateBlock->setAuthorityTo($authorityTo);
            $deliveryDateBlock->setDeliveryComment($order->getDeliveryComment());
            $deliveryDateBlock->setTemplate('PHPMechanic_DeliveryDate::order_info_shipping_info.phtml');
            $html = $observer->getTransport()->getOutput() . $deliveryDateBlock->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}