<?php
/**
 * Blackbird EstimateTimeShipping Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_EstimateTimeShipping
 * @copyright       Copyright (c) 2018 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://store.bird.eu/license/
 * @support         help@bird.eu
 */

namespace Blackbird\EstimateTimeShipping\Controller\Estimation;

use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class QuoteDate
 * @package Blackbird\EstimateTimeShipping\Controller\Estimation
 */
class QuoteDate extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ShippingTimeRule\CollectionFactory
     */
    protected $shippingTimeRuleCollectionFactory;

    /**
     * @var ShippingTimeRuleRepositoryInterface
     */
    protected $shippingTimeRuleRepo;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Quote\Item
     */
    protected $quoteItem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * QuoteDate constructor.
     * @param Context $context
     * @param QuoteRepository $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helper
     * @param ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Session $checkoutSession
     * @param Quote\Item $quoteItem
     * @param ShippingTimeRule\CollectionFactory $shippingTimeRuleCollectionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        QuoteRepository $quoteRepository,
        ProductRepositoryInterface $productRepository,
        Data $helper,
        ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Session $checkoutSession,
        Quote\Item $quoteItem,
        ShippingTimeRule\CollectionFactory $shippingTimeRuleCollectionFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->date                              = $localeDate;
        $this->shippingTimeRuleRepo              = $shippingTimeRuleRepository;
        $this->shippingTimeRuleCollectionFactory = $shippingTimeRuleCollectionFactory;
        $this->quoteRepository                   = $quoteRepository;
        $this->resultJsonFactory                 = $resultJsonFactory;
        $this->productRepository                 = $productRepository;
        $this->helper                            = $helper;
        $this->checkoutSession                   = $checkoutSession;
        $this->quoteItem                         = $quoteItem;
        parent::__construct($context);
    }

    /**
     * Send the latest estimated date
     *
     * @return mixed
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $type   = $params['type'];

        $resultJson = $this->resultJsonFactory->create();
        $quote      = $this->checkoutSession->getQuote();
        $isDelivery = false;

        if (isset($params['address']) && $type != "product") {
            $address        = json_decode($params['address'], true);
            $shippingMethod = isset($params['method']) ? $params['method'] : null;
            $this->handleAddress($quote, $address, $shippingMethod);
        }

        /** Get the shipping date for all cart item and for current product */
        if ($type == 'cart') {
            $dates = [];

            foreach ($quote->getItemsCollection() as $item) {
                $product = $this->productRepository->get($item->getData('sku'));
                /** @var \DateTime $date */
                if ($date = $this->helper->getEstimatedDateByProduct($product, $quote)) {
                    $dates[] = $date->format('m/d/Y');
                }
            }

            $shippingDate = (!empty($dates)) ? $this->date->date(strtotime(max($dates))) : null;
        } elseif ($type == 'checkout') {
            $item         = $this->quoteItem->load($params['currentSku']);
            $product      = $this->productRepository->get($item->getSku());
            $shippingDate = $this->helper->getEstimatedDateByProduct($product, $quote);
        } else {
            $currentSku = $params['currentSku'];
            $product    = $this->productRepository->get($currentSku);

            if ($type == "product") {
                $quote = clone $quote;
                $qty   = $params['qty'];
                for ($i = 0; $i < $qty; $i++) {
                    $quote->addProduct($product);
                }
                $quote->collectTotals();
            }

            $shippingDate = $this->helper->getEstimatedDateByProduct($product, $quote);
        }

        $shippingTimeRules = $this->shippingTimeRuleCollectionFactory->create()
            ->addFieldToSelect('shipping_time_rule_id')
            ->addFieldToFilter('is_active', true)
            ->getData();

        /** Check if rules are matching for current product(s) and cart */
        $hasCartMatched = false;

        for ($i = 0; $i < count($shippingTimeRules) && !$hasCartMatched; $i++) {
            $rule           = $this->shippingTimeRuleRepo->getById($shippingTimeRules[$i]['shipping_time_rule_id']);
            $hasCartMatched = $rule->isQuoteMatching($quote);
            if ($hasCartMatched) {
                $isDelivery   = true;
                $shippingDate = $rule->getEstimatedShippingTime($shippingDate);
            }
        }

        $shippingDate = ($shippingDate) ? $this->date->formatDate($shippingDate, $this->helper->getDateFormat()) : null;

        $displayIfNotExist = $this->helper->getDisplayIfNoDate();
        $message           = $this->getMessage($shippingDate, $isDelivery);

        return $resultJson->setData([
            'preparationDate' => $message,
            'dateExist'       => boolval($shippingDate),
            'display'         => $displayIfNotExist,
            'checkoutDisplay' => $this->getCheckoutDisplay($type)
        ]);
    }

    /**
     * Get for the checkout the type of displlay
     *
     * @param $type
     * @return null|string
     */
    protected function getCheckoutDisplay($type)
    {
        $howToDisplay = $this->helper->getHowToDisplay();

        if (($type == 'checkout' || $type == 'cart')) {
            if ($this->helper->getOrderCheckoutDisplay() && $howToDisplay) {
                return 'all';
            }

            if ($this->helper->getProductCheckoutDisplay() && !$howToDisplay) {
                return 'items';
            }
        }

        return false;
    }

    /**
     * Get the configurable message for each case
     *
     * @param $date
     * @param $isDelivery
     * @return \Magento\Framework\Phrase
     */
    protected function getMessage($date, $isDelivery)
    {
        if ($date === null) {
            return __($this->helper->getNoDateMessages());
        }

        if ($this->helper->getHowToDisplay()) {
            if ($isDelivery) {
                return __($this->helper->getOrderDeliveryDateMessages(), $date);
            } else {
                return __($this->helper->getOrderShippingDateMessages(), $date);
            }
        } else {
            if ($isDelivery) {
                return __($this->helper->getProductDeliveryDateMessages(), $date);
            } else {
                return __($this->helper->getProductShippingDateMessages(), $date);
            }
        }
    }

    /**
     * Set address information if customer change her in cart
     *
     * @param Quote $quote
     * @param $addressData
     * @param $shippingMethod
     */
    protected function handleAddress(&$quote, $addressData, $shippingMethod)
    {
        $quote->getShippingAddress()->setRegionId($addressData['region_id']);
        $quote->getShippingAddress()->setRegion($addressData['region']);
        $quote->getShippingAddress()->setCountryId($addressData['country_id']);
        $quote->getShippingAddress()->setPostcode($addressData['postcode']);
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);
    }
}
