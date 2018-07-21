<?php
/**
 * Widget that adds Olark Live Chat to Magento stores.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@olark.com so we can send you a copy immediately.
 *
 * @category    Olark
 * @package     Olark_Chatbox
 * @copyright   Copyright 2012. Habla, Inc. (http://www.olark.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Olark\Chatbox\Block;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * Customer data
     *
     * @var array
     */
    private $customerData = null;

    /**
     * Product data
     *
     * @var array
     */
    private $productsData = null;

    /**
     * Magento data
     *
     * @var array
     */
    private $magentoData = null;

    /**
     * @var \Olark\Chatbox\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Returns Site ID
     *
     * @return string
     */
    abstract public function getSiteId();

    /**
     * Returns Developer API snippets
     *
     * @return string
     */
    abstract public function getCustomConfig();

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Olark\Chatbox\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     * @codingStandardsIgnoreStart
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Olark\Chatbox\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Returns the version of this Olark CartSaver plugin.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->helper->getModuleVersion();
    }

    /**
     * Returns customer's data
     *
     * @return array
     */
    public function getCustomerData()
    {
        if ($this->customerData === null) {
            $customer = [];

            $customerInfo = $this->customerSession->getCustomer();
            if ($customerInfo) {
                $billingAddressText = '';
                $shippingAddressText = '';

                $billingAddress = $customerInfo->getPrimaryBillingAddress();
                if ($billingAddress) {
                    $billingAddressText = $billingAddress->getConfig()
                        ->getFormatByCode('text')
                        ->getRenderer()
                        ->render($billingAddress);
                }

                $shippingAddress = $customerInfo->getPrimaryShippingAddress();
                if ($billingAddress) {
                    $shippingAddressText = $shippingAddress->getConfig()
                        ->getFormatByCode('text')
                        ->getRenderer()
                        ->render($shippingAddress);
                }

                $customer = [
                    'name' => $customerInfo->getName(),
                    'email' => $customerInfo->getEmail(),
                    'billing_address' => $billingAddressText,
                    'shipping_address' => $shippingAddressText,
                ];
            }
            $this->customerData = $customer;
        }
        return $this->customerData;
    }

    /**
     * Returns customer's data json encoded
     *
     * @return string
     */
    public function getCustomerDataJson()
    {
        return \Zend_Json::encode($this->getCustomerData());
    }

    /**
     * Returns magento data
     *
     * @return array
     */
    public function getMagentoData()
    {
        if ($this->magentoData === null) {
            $recentEvents = $this->_popRecentEvents();

            $totalValueOfItems = 0.0;
            $items = $this->getProductsData();
            if (!empty($items)) {
                foreach ($items as $product) {
                    $totalValueOfItems += ($product['price'] * $product['quantity']);
                }
            }

            $extraItems = [];

            // Attempt to get totals from Magento directly.
            $totals = $this->checkoutSession->getQuote()->getTotals();
            foreach ($totals as $total) {
                $extraItems[] = [
                    'name' => $total->getCode(),
                    'price' => $total->getValue(),
                    'formatted_price' => $this->pricingHelper->currency($total->getValue(), true, false),
                ];
                if ('subtotal' == $total->getCode()) {
                    $totalValueOfItems = floatval($total->getValue());
                }
            }

            $magentoData = [
                'total' => $totalValueOfItems,
                'formatted_total' => $this->pricingHelper->currency($totalValueOfItems, true, false),
                'extra_items' => $extraItems,
                'recent_events' => $recentEvents
            ];
            $this->magentoData = $magentoData;
        }
        return $magentoData;
    }

    /**
     * Returns magento data
     *
     * @return array
     */
    public function getMagentoDataJson()
    {
        return \Zend_Json::encode($this->getMagentoData());
    }

    /**
     * Returns products data
     *
     * @return array
     */
    public function getProductsData()
    {
        if ($this->productsData === null) {
            $products = [];

            $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
            if (!empty($items)) {
                foreach ($items as $item) {
                    // Capture the raw item in case we want to show
                    // more information in the future without updating
                    // our Magento app.
                    $magentoItem = $item->getData();
                    $magentoItem['formatted_price'] = $this->pricingHelper->currency($item->getPrice(), true, false);

                    $products[] = [
                        'name' => $item->getName(),
                        'sku' => $item->getSku(),
                        'quantity' => $item->getQty(),
                        'price' => $item->getPrice(),
                        'magento' => $magentoItem
                    ];
                }
            }

            $this->productsData = $products;
        }
        return $this->productsData;
    }

    /**
     * Returns products data json encoded
     *
     * @return string
     */
    public function getProductsDataJson()
    {
        return \Zend_Json::encode($this->getProductsData());
    }

    /**
     * Produces Olark Chatbox html
     *
     * @return string
     */
    public function _toHtml()
    {
        // Don't show the Olark code at all if there is no Site ID.
        if (!$this->getSiteId()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Pops the list of recent events.  This empties the olark_chat_events list
     * after returning the recent events.
     *
     * @return array
     */
    private function _popRecentEvents()
    {
        return $this->_session->getData('olark_chatbox_events', true);
    }
}
