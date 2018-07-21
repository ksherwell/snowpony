<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use PL\Payway\Model\Api\PayWayAPI;
use Magento\Framework\Exception\LocalizedException;

/** @noinspection PhpDeprecationInspection */
class Payway extends \Magento\Payment\Model\Method\Cc
{
    const METHOD_CODE = 'payway';

    const STATUS_APPROVED = 'Approved';

    const PAYMENT_ACTION_AUTH_CAPTURE = 'authorize_capture';

    const PAYMENT_ACTION_AUTH = 'authorize';

    protected $_code = self::METHOD_CODE;

    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canCaptureOnce = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_isGateway = true;

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
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Payway constructor.
     * @param \PL\Payway\Helper\Data $paywayHelper
     * @param \PL\Payway\Logger\Logger $plLogger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \PL\Payway\Helper\Data $paywayHelper,
        \PL\Payway\Logger\Logger $plLogger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
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
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->paywayHelper = $paywayHelper;
        $this->plLogger = $plLogger;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function validate()
    {
        /** @noinspection PhpDeprecationInspection */
        parent::validate();
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof \Magento\Sales\Model\Order\Payment) {
            $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $paymentInfo->getQuote()->getBaseCurrencyCode();
        }
        return $this;
    }

    public function getPaymentAction()
    {
        if ($this->getConfigData('payment_action') == 'authorize') {
            return 'preauth';
        }
        return 'capture';
    }
    
    public function get3DSecure()
    {
        return $this->getConfigData('threedsecure');
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $storeId = $payment->getOrder()->getStoreId();
        $payment->setCcTransId($this->getOrderPrefix($storeId).$payment->getOrder()->getIncrementId());
        $this->setAmount($amount)->setPayment($payment);
        $errorMessage = false;
        try {
            $result = $this->processRequest($payment);
            if ($result['response.summaryCode'] === '0') {
                $payment
                    ->setStatus(self::STATUS_APPROVED)
                    ->setTransactionId($result['response.receiptNo'])
                    ->setIsTransactionClosed(0);
            } else {
                $errorMessage = "Error " . $result["response.responseCode"] . ": " . $result["response.text"];
            }

        } catch(\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        if ($errorMessage) {
            throw new LocalizedException($this->paywayHelper->wrapGatewayError($errorMessage));
        }

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $storeId = $payment->getOrder()->getStoreId();
        $payment->setCcTransId($this->getOrderPrefix($storeId).$payment->getOrder()->getIncrementId());
        $this->setAmount($amount)->setPayment($payment);
        $errorMessage = false;
        try {
            $result = $this->processRequest($payment);
            if ($result['response.summaryCode'] === '0') {
                $payment
                    ->setStatus(self::STATUS_APPROVED)
                    ->setTransactionId($result['response.receiptNo'])
                    ->setIsTransactionClosed(0);
            } else {
                $errorMessage = "Error " . $result["response.responseCode"] . ": " . $result["response.text"];
            }
        } catch(\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        if ($errorMessage) {
            throw new LocalizedException($this->paywayHelper->wrapGatewayError($errorMessage));
        }
        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->setAmount($amount)->setPayment($payment);
        $errorMessage = false;
        try {
            $result = $this->processRefund($payment);
            if ($result['response.summaryCode'] === '0') {
                $payment
                    ->setStatus(self::STATUS_APPROVED)
                    ->setTransactionId($result['response.receiptNo'])
                    ->setIsTransactionClosed(1);
            } else {
                $errorMessage = "Error " . $result["response.responseCode"] . ": " . $result["response.text"];
            }
        } catch(\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        if ($errorMessage) {
            throw new LocalizedException($this->paywayHelper->wrapGatewayError($errorMessage));
        }
        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $type
     * @return array
     */
    protected function processRequest(\Magento\Payment\Model\InfoInterface $payment)
    {
        $storeId = $payment->getOrder()->getStoreId();
        $init = "certificateFile=" . $this->getCertificate() . "&" .
            "caFile=" . $this->getCaFile() . "&" .
            "logDirectory=" . $this->getLogDir();
        $payWayApi = new PayWayAPI;
        $payWayApi->initialise($init);
        $params = [];
        $params["order.type"] = $this->getPaymentAction();
        $params["customer.username"] = $this->getConfigData('username', $storeId);
        $params["customer.password"] = $this->getConfigData('password', $storeId);
        $params["customer.merchant"] = $this->getConfigData('merchant_id', $storeId);
        $params["card.PAN"] = $payment->getCcNumber();
        $params["card.CVN"] = $payment->getCcCid();
        $params["card.expiryYear"] = substr($payment->getCcExpYear(), 2, 2);
        $params["card.expiryMonth"] = str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT);
        $params["customer.orderNumber"] = $payment->getCcTransId();
        $params["card.currency"] = $payment->getOrder()->getBaseCurrencyCode();
        $params["order.amount"] = $this->getAmount() * 100;
        if ($this->get3DSecure()) {
            $params["order.ECI"] = "5";
            $params["order.xid"] = $payment->getOrder()->getIncrementId();
            $params["order.cavv"] = base64_encode($params["order.xid"]);
        } else {
            $params["order.ECI"] = "SSL";
        }

        $requestText = $payWayApi->formatRequestParameters($params);
        $responseText = $payWayApi->processCreditCard($requestText);
        $result = $payWayApi->parseResponseParameters($responseText);
        return $result;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return array
     */
    protected function processRefund(\Magento\Payment\Model\InfoInterface $payment)
    {
        $storeId = $payment->getOrder()->getStoreId();
        $init = "certificateFile=" . $this->getCertificate() . "&" .
            "caFile=" . $this->getCaFile() . "&" .
            "logDirectory=" . $this->getLogDir();
        $payWayApi = new PayWayAPI;
        $payWayApi->initialise($init);
        $params = [];
        $params["order.type"] = "refund";
        $params["customer.username"] = $this->getConfigData('username', $storeId);
        $params["customer.password"] = $this->getConfigData('password', $storeId);
        $params["customer.merchant"] = $this->getConfigData('merchant_id', $storeId);
        $params["card.expiryYear"] = substr($payment->getCcExpYear(), 2, 2);
        $params["card.expiryMonth"] = str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT);
        $params["customer.orderNumber"] = $payment->getCcTransId()."R";
        $params["customer.originalOrderNumber"] = $payment->getCcTransId();
        $params["card.currency"] = $payment->getOrder()->getBaseCurrencyCode();
        $params["order.amount"] = $this->getAmount() * 100;
        $params["order.ECI"] = "SSL";
        $requestText = $payWayApi->formatRequestParameters($params);
        $responseText = $payWayApi->processCreditCard($requestText);
        $result = $payWayApi->parseResponseParameters($responseText);
        return $result;
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
    public function getCertificate()
    {
        return dirname(__FILE__) . '/Api/ccapi.pem';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return  $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath().'log/pl/payway/';
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getOrderPrefix($storeId)
    {
        if ($this->getConfigData('order_prefix', $storeId) !="") {
            return substr($this->getConfigData('order_prefix', $storeId), 0, 8);
        } else {
            return '';
        }
    }
}
