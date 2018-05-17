<?php
namespace Amazon\Payment\Model\Method\Amazon;

/**
 * Interceptor class for @see \Amazon\Payment\Model\Method\Amazon
 */
class Interceptor extends \Amazon\Payment\Model\Method\Amazon implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Payment\Model\Method\Logger $logger, \Amazon\Core\Client\ClientFactoryInterface $clientFactory, \Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory $quoteLinkFactory, \Amazon\Payment\Api\OrderInformationManagementInterface $orderInformationManagement, \Magento\Quote\Api\CartRepositoryInterface $cartRepository, \Amazon\Payment\Domain\AmazonAuthorizationResponseFactory $amazonAuthorizationResponseFactory, \Amazon\Payment\Domain\AmazonCaptureResponseFactory $amazonCaptureResponseFactory, \Amazon\Payment\Domain\AmazonRefundResponseFactory $amazonRefundResponseFactory, \Amazon\Payment\Domain\AmazonAuthorizationDetailsResponseFactory $amazonAuthorizationDetailsResponseFactory, \Amazon\Payment\Domain\Validator\AmazonAuthorization $amazonAuthorizationValidator, \Amazon\Payment\Domain\Validator\AmazonPreCapture $amazonPreCaptureValidator, \Amazon\Payment\Domain\Validator\AmazonCapture $amazonCaptureValidator, \Amazon\Payment\Domain\Validator\AmazonRefund $amazonRefundValidator, \Amazon\Payment\Model\PaymentManagement $paymentManagement, \Amazon\Core\Helper\Data $amazonCoreHelper, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $clientFactory, $quoteLinkFactory, $orderInformationManagement, $cartRepository, $amazonAuthorizationResponseFactory, $amazonCaptureResponseFactory, $amazonRefundResponseFactory, $amazonAuthorizationDetailsResponseFactory, $amazonAuthorizationValidator, $amazonPreCaptureValidator, $amazonCaptureValidator, $amazonRefundValidator, $paymentManagement, $amazonCoreHelper, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function denyPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'denyPayment');
        if (!$pluginInfo) {
            return parent::denyPayment($payment);
        } else {
            return $this->___callPlugins('denyPayment', func_get_args(), $pluginInfo);
        }
    }
}
