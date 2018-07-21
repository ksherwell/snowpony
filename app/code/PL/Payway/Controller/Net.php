<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Controller;

abstract class Net extends \Magento\Framework\App\Action\Action
{
    protected $paywayHelper;

    protected $plLogger;

    protected $checkoutSession;

    protected $storeManager;

    protected $orderFactory;

    /**
     * Net constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \PL\Payway\Helper\Data $paywayHelper
     * @param \PL\Payway\Logger\Logger $plLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PL\Payway\Helper\Data $paywayHelper,
        \PL\Payway\Logger\Logger $plLogger
    ) {
        parent::__construct($context);
        $this->paywayHelper = $paywayHelper;
        $this->plLogger = $plLogger;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
    }
}