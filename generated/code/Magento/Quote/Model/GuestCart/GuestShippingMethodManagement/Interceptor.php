<?php
namespace Magento\Quote\Model\GuestCart\GuestShippingMethodManagement;

/**
 * Interceptor class for @see \Magento\Quote\Model\GuestCart\GuestShippingMethodManagement
 */
class Interceptor extends \Magento\Quote\Model\GuestCart\GuestShippingMethodManagement implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement, \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory)
    {
        $this->___init();
        parent::__construct($shippingMethodManagement, $quoteIdMaskFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function estimateByExtendedAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'estimateByExtendedAddress');
        if (!$pluginInfo) {
            return parent::estimateByExtendedAddress($cartId, $address);
        } else {
            return $this->___callPlugins('estimateByExtendedAddress', func_get_args(), $pluginInfo);
        }
    }
}
