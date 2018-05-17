<?php
namespace Magento\Checkout\Model\GuestTotalsInformationManagement;

/**
 * Interceptor class for @see \Magento\Checkout\Model\GuestTotalsInformationManagement
 */
class Interceptor extends \Magento\Checkout\Model\GuestTotalsInformationManagement implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory, \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement)
    {
        $this->___init();
        parent::__construct($quoteIdMaskFactory, $totalsInformationManagement);
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($cartId, \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'calculate');
        if (!$pluginInfo) {
            return parent::calculate($cartId, $addressInformation);
        } else {
            return $this->___callPlugins('calculate', func_get_args(), $pluginInfo);
        }
    }
}
