<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Controller\Net;

class Notify extends \PL\Payway\Controller\Net
{
    protected $net;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PL\Payway\Helper\Data $paywayHelper,
        \PL\Payway\Logger\Logger $plLogger,
        \PL\Payway\Model\Net $net
    ) {
        parent::__construct(
            $context,
            $orderFactory,
            $checkoutSession,
            $storeManager,
            $paywayHelper,
            $plLogger
        );
        $this->net = $net;
    }


    public function execute()
    {
        $encryptedParametersText = $this->getRequest()->getParam('EncryptedParameters');
        $signatureText = $this->getRequest()->getParam('Signature');
        $encryptionKey = $this->net->getConfigData('encryption_key');
        $parameters = $this->paywayHelper->decryptParameters($encryptionKey, $encryptedParametersText, $signatureText);
        if ($this->net->getConfigData('debug')) {
            $this->plLogger->debug(print_r($parameters, 1));
        }
        if (isset($parameters['payment_reference'])) {
            $incrementId = trim(stripslashes($parameters['payment_reference']));
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
            if ($order->getId()) {
                if (isset($parameters['summary_code']) && $parameters['summary_code'] == '0') {
                    $this->net->acceptTransaction($order, $parameters);
                    //$this->checkoutSession->clearQuote()->clearStorage();
                    $this->_redirect('checkout/onepage/success');
                } else {
                    $this->messageManager->addError(__('Transaction was Declined.'));
                    $this->net->rejectTransaction($order, $parameters);
                    $this->checkoutSession->clearQuote()->clearStorage();
                    $this->_redirect('checkout/cart');
                }
            }
        } else {
            $this->messageManager->addError(__('There has been an error processing your payment.'));
            $this->_redirect('checkout/cart');
        }
    }
}
