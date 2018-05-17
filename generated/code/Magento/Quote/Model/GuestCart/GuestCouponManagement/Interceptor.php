<?php
namespace Magento\Quote\Model\GuestCart\GuestCouponManagement;

/**
 * Interceptor class for @see \Magento\Quote\Model\GuestCart\GuestCouponManagement
 */
class Interceptor extends \Magento\Quote\Model\GuestCart\GuestCouponManagement implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Api\CouponManagementInterface $couponManagement, \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory)
    {
        $this->___init();
        parent::__construct($couponManagement, $quoteIdMaskFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'get');
        if (!$pluginInfo) {
            return parent::get($cartId);
        } else {
            return $this->___callPlugins('get', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'set');
        if (!$pluginInfo) {
            return parent::set($cartId, $couponCode);
        } else {
            return $this->___callPlugins('set', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'remove');
        if (!$pluginInfo) {
            return parent::remove($cartId);
        } else {
            return $this->___callPlugins('remove', func_get_args(), $pluginInfo);
        }
    }
}
