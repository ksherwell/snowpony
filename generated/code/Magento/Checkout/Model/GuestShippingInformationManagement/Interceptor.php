<?php
namespace Magento\Checkout\Model\GuestShippingInformationManagement;

/**
 * Interceptor class for @see \Magento\Checkout\Model\GuestShippingInformationManagement
 */
class Interceptor extends \Magento\Checkout\Model\GuestShippingInformationManagement implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory, \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement)
    {
        $this->___init();
        parent::__construct($quoteIdMaskFactory, $shippingInformationManagement);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAddressInformation($cartId, \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'saveAddressInformation');
        if (!$pluginInfo) {
            return parent::saveAddressInformation($cartId, $addressInformation);
        } else {
            return $this->___callPlugins('saveAddressInformation', func_get_args(), $pluginInfo);
        }
    }
}
