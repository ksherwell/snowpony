<?php
namespace Magento\Quote\Model\Cart\CartTotalManagement;

/**
 * Interceptor class for @see \Magento\Quote\Model\Cart\CartTotalManagement
 */
class Interceptor extends \Magento\Quote\Model\Cart\CartTotalManagement implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement, \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement, \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository, \Magento\Quote\Model\Cart\TotalsAdditionalDataProcessor $dataProcessor)
    {
        $this->___init();
        parent::__construct($shippingMethodManagement, $paymentMethodManagement, $cartTotalsRepository, $dataProcessor);
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
