<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Model;

class Net extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'payway_net';

    const PAYWAY_URL = 'https://payway.stgeorge.com.au/';

    protected $_code = self::METHOD_CODE;

    protected $_infoBlockType = 'PL\Payway\Block\Info\Net';

    protected $_isInitializeNeeded      = true;

    protected $_canUseCheckout          = true;

    protected $_canUseInternal          = false;

    protected $_canUseForMultishipping  = false;

    /**
     * @var \PL\Payway\Helper\Data
     */
    protected $paywayHelper;

    /**
     * @var \PL\Payway\Logger\Logger
     */
    protected $plLogger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolver;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected $jsonHelper;

    protected $orderSender;

    protected $invoiceSender;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \PL\Payway\Helper\Data $paywayHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \PL\Payway\Logger\Logger $plLogger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        /** @noinspection PhpDeprecationInspection */
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->paywayHelper = $paywayHelper;
        $this->storeManager = $storeManager;
        $this->resolver = $resolver;
        $this->plLogger = $plLogger;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }

    public function initialize($paymentAction, $stateObject)
    {
        if ($paymentAction == 'order') {
            $order = $this->getInfoInstance()->getOrder();
            $order->setCustomerNoteNotify(false);
            $order->setCanSendNewEmailFlag(false);
            $comment = __('Redirecting to the payment gateway. Order #%1', $order->getIncrementId());
            $order->setCustomerNote($comment);
            $stateObject->setIsNotified(false);
            $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $stateObject->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        }
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see \Magento\Quote\Model\Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->urlBuilder->getUrl('payway/net/redirect', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return string
     */
    public function getReturnLinkUrl()
    {
        return $this->urlBuilder->getUrl('payway/net/notify', ['_secure' => $this->getRequest()->isSecure()]);
    }




    public function getFormFields()
    {
        $paymentInfo = $this->getInfoInstance();
        $order = $paymentInfo->getOrder();
        $billerCode = $this->getConfigData('biller_code');
        $merchantId = $this->getConfigData('merchant_id');
        $paypalEmail = $this->getConfigData('paypal_email');
        $securityUsername = $this->getConfigData('security_username');
        $securityPassword = $this->getConfigData('security_password');
        $paymentAmount = $order->getBaseGrandTotal();
        $referenceNumber = $order->getIncrementId();
        $token_variables = [
            "username" => $securityUsername,
            "password" => $securityPassword,
            "biller_code" => $billerCode,
            "merchant_id" => $merchantId,
            "paypal_email" => $paypalEmail,
            "payment_reference" => $referenceNumber,
            "payment_amount" => $paymentAmount,
            "return_link_url"=> $this->getReturnLinkUrl(),
            "return_link_redirect"=> 'true',
            "return_link_payment_status"=>"all"
        ];
        $token = $this->getToken($token_variables);
        return [
            'biller_code' => $billerCode,
            'token' => $token
        ];
    }

    /**
     * @param $parameters
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getToken($parameters)
    {
        $proxyHostSetting = $this->getConfigData('proxy_host');
        $proxyPortSetting = $this->getConfigData('proxy_port');
        $proxyUserSetting = $this->getConfigData('proxy_username');
        $proxyPasswordSetting = $this->getConfigData('proxy_password');
        $proxyHost = strlen($proxyHostSetting) == 0 ? null : $proxyHostSetting;
        $proxyPort = strlen($proxyPortSetting) == 0 ? null : $proxyPortSetting;
        $proxyUser = strlen($proxyUserSetting) == 0 ? null : $proxyUserSetting;
        $proxyPassword = strlen($proxyPasswordSetting) == 0 ? null : $proxyPasswordSetting;
        $caCertsFile = $this->getCaFile();
        $payWayUrl = self::PAYWAY_URL;
        // Find the port setting, if any.
        $port = 443;
        $portPos = strpos($payWayUrl, ":", 6);
        $urlEndPos = strpos($payWayUrl, "/", 8);
        if ($portPos !== false && $portPos < $urlEndPos) {
            $port = (int)substr($payWayUrl, ((int)$portPos) + 1, ((int)$urlEndPos));
            $payWayUrl = substr($payWayUrl, 0, ((int)$portPos))
                . substr($payWayUrl, ((int)$urlEndPos), strlen($payWayUrl));
        }

        $ch = curl_init($payWayUrl . "RequestToken");
        if ($port != 443) {
            curl_setopt($ch, CURLOPT_PORT, $port);
        }
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set proxy information as required
        if (!is_null($proxyHost) && !is_null($proxyPort)) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXY, $proxyHost . ":" . $proxyPort);
            if (!is_null($proxyUser)) {
                if (is_null($proxyPassword)) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUser . ":");
                } else {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUser . ":" . $proxyPassword);
                }
            }
        }

        // Set timeout options
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Set references to certificate files\
        if (file_exists($caCertsFile)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertsFile);
        }

        // Check the existence of a common name in the SSL peer's certificate
        // and also verify that it matches the hostname provided
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Verify the certificate of the SSL peer
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        // Build the parameters string to pass to PayWay
        $parametersString = '';
        $init = true;
        foreach ($parameters as $paramName => $paramValue) {
            if ($init) {
                $init = false;
            } else {
                $parametersString = $parametersString . '&';
            }
            $parametersString = $parametersString . urlencode($paramName) . '=' . urlencode($paramValue);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $parametersString);

        // Make the request
        $responseText = curl_exec($ch);

        // Check the response for errors
        $errorNumber = curl_errno($ch);
        if ($errorNumber != 0) {
            $message =  __("CURL Error: %1", curl_error($ch));
            throw new \Magento\Framework\Exception\LocalizedException($message);
        }
        curl_close($ch);

        // Split the response into parameters
        $responseParameterArray = explode("&", $responseText);
        $responseParameters = [];
        foreach ($responseParameterArray as $responseParameter) {
            list($paramName, $paramValue) = explode("=", $responseParameter, 2);
            $responseParameters[ $paramName ] = $paramValue;
        }
        if (array_key_exists('error', $responseParameters)) {
            $message = __("Error getting token: %1", $responseParameters['error']);
            throw new \Magento\Framework\Exception\LocalizedException($message);
        } else {
            return $responseParameters['token'];
        }
    }

    /**
     * @return string
     */
    public function getCaFile()
    {
        return dirname(__FILE__) . '/Api/cacerts.crt';
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return self::PAYWAY_URL.'MakePayment';
    }

    public function acceptTransaction(\Magento\Sales\Model\Order $order, $response = [])
    {
        $this->checkOrderStatus($order);
        if ($order->getId()) {
            $additionalData = $this->jsonHelper->jsonEncode($response);
            $order->getPayment()->setTransactionId($response['payment_number']);
            $order->getPayment()->setLastTransId($response['payment_number']);
            $order->getPayment()->setAdditionalInformation('payment_additional_info', $additionalData);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $note = __('Approved the payment online. Transaction ID: "%1"', $response['payment_number']);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->addStatusHistoryComment($note);
            $order->save();
            $this->orderSender->send($order);
            if (!$order->hasInvoices() && $order->canInvoice()) {
                $invoice = $order->prepareInvoice();
                if ($invoice->getTotalQty() > 0) {
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    $invoice->setTransactionId($order->getPayment()->getTransactionId());
                    $invoice->register();
                    $invoice->addComment(__('Automatic invoice.'), false);
                    $invoice->save();
                    $this->invoiceSender->send($invoice);
                }
            }
        }
    }

    public function rejectTransaction(\Magento\Sales\Model\Order $order, $response = [])
    {
        $this->checkOrderStatus($order);
        if ($order->getId()) {
            $note = 'Your order has been cancelled';
            if (isset($response['payment_number'])) {
                $additionalData = $this->jsonHelper->jsonEncode($response);
                $order->getPayment()->setTransactionId($response['payment_number']);
                $order->getPayment()->setLastTransId($response['payment_number']);
                $order->getPayment()->setAdditionalInformation('payment_additional_info', $additionalData);
                $note = __('Transaction was declined. Transaction ID: "%1"', $response['payment_number']);
            }
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->addStatusHistoryComment($note);
            $order->save();
        }
    }

    public function checkOrderStatus($order)
    {
        if ($order->getId()) {
            $state = $order->getState();
            switch ($state) {
                case \Magento\Sales\Model\Order::STATE_HOLDED:
                case \Magento\Sales\Model\Order::STATE_CANCELED:
                case \Magento\Sales\Model\Order::STATE_CLOSED:
                case \Magento\Sales\Model\Order::STATE_COMPLETE:
                    break;
            }
        }
    }
}
