<?php
namespace Vertex\Tax\Controller\Adminhtml\ManualInvoice\Send;

/**
 * Interceptor class for @see \Vertex\Tax\Controller\Adminhtml\ManualInvoice\Send
 */
class Interceptor extends \Vertex\Tax\Controller\Adminhtml\ManualInvoice\Send implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Sales\Api\OrderRepositoryInterface $orderRepository, \Vertex\Tax\Model\Config $config, \Vertex\Tax\Model\CountryGuard $countryGuard, \Vertex\Tax\Model\TaxInvoice $taxInvoice)
    {
        $this->___init();
        parent::__construct($context, $orderRepository, $config, $countryGuard, $taxInvoice);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
