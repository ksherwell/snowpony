<?php
namespace Magento\Quote\Model\GuestCart\GuestCartTotalManagement;

/**
 * Interceptor class for @see \Magento\Quote\Model\GuestCart\GuestCartTotalManagement
 */
class Interceptor extends \Magento\Quote\Model\GuestCart\GuestCartTotalManagement implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Api\CartTotalManagementInterface $cartTotalManagement, \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory)
    {
        $this->___init();
        parent::__construct($cartTotalManagement, $quoteIdMaskFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function collectTotals($cartId, \Magento\Quote\Api\Data\PaymentInterface $paymentMethod, $shippingCarrierCode = null, $shippingMethodCode = null, \Magento\Quote\Api\Data\TotalsAdditionalDataInterface $additionalData = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'collectTotals');
        if (!$pluginInfo) {
            return parent::collectTotals($cartId, $paymentMethod, $shippingCarrierCode, $shippingMethodCode, $additionalData);
        } else {
            return $this->___callPlugins('collectTotals', func_get_args(), $pluginInfo);
        }
    }
}
