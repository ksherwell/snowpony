<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use PL\Payway\Helper\Data as PaywayHelper;

class NetConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;


    protected $paywayHelper;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        'payway_net'
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;


    public function __construct(
        CurrentCustomer $currentCustomer,
        PaywayHelper $paywayHelper,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->paywayHelper = $paywayHelper;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }

    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                'paywayNet' => []
            ]
        ];

        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['paywayNet']['redirectUrl'] = $this->getMethodRedirectUrl($code);
            }
        }

        return $config;
    }

    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }
}
