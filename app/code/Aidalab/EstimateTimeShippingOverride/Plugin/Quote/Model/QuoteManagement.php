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

namespace Aidalab\EstimateTimeShippingOverride\Plugin\Quote\Model;

use Blackbird\EstimateTimeShipping\Api\Data\EstimatedDateInterface;
use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Blackbird\EstimateTimeShipping\Model\EstimatedDateFactory;
use Blackbird\EstimateTimeShipping\Model\EstimatedDateRepository;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Class QuoteManagement
 * @package Blackbird\EstimateTimeShipping\Plugin\Quote\Model
 */
class QuoteManagement extends \Blackbird\EstimateTimeShipping\Plugin\Quote\Model\QuoteManagement
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var EstimatedDateFactory
     */
    protected $estimatedDateFactory;

    /**
     * @var EstimatedDateRepository
     */
    protected $estimatedDateRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CollectionFactory
     */
    protected $shippingTimeRuleCollectionFactory;

    /**
     * @var ShippingTimeRuleRepositoryInterface
     */
    protected $shippingTimeRuleRepository;

    /**
     * @var bool
     */
    protected $isDelivery = false;

    /**
     * QuoteManagement constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QuoteRepository $quoteRepository,
        EstimatedDateFactory $estimatedDateFactory,
        EstimatedDateRepository $estimatedDateRepository,
        CollectionFactory $shippingTimeRuleCollectionFactory,
        ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository,
        ProductRepositoryInterface $productRepository,
        Data $helper
    ) {
        $this->orderRepository                   = $orderRepository;
        $this->quoteRepository                   = $quoteRepository;
        $this->estimatedDateFactory              = $estimatedDateFactory;
        $this->estimatedDateRepository           = $estimatedDateRepository;
        $this->productRepository                 = $productRepository;
        $this->shippingTimeRuleRepository        = $shippingTimeRuleRepository;
        $this->shippingTimeRuleCollectionFactory = $shippingTimeRuleCollectionFactory;
        $this->helper                            = $helper;
    }

    /**
     * After order is placed, we stock the estimated delivery date, for each items
     *
     * @param \Magento\Sales\Model\Service\OrderService $subject
     * @param Order $order
     * @return mixed
     */
    public function afterPlace(\Magento\Sales\Model\Service\OrderService $subject, $order)
    {
        $quote = $this->quoteRepository->get($order->getQuoteId());

        /** @var EstimatedDateInterface $estimatedDate */
        foreach ($order->getItems() as $item) {
            foreach ($quote->getItems() as $quoteItem) {
                $estimatedDate = $this->estimatedDateFactory->create();
                $product = $this->productRepository->get($item->getSku());

                $estimatedDate->setQuoteId($quote->getId());
                $estimatedDate->setQuoteItemId($quoteItem->getItemId()); //Get quoteItemId from Quote
                $estimatedDate->setOrderId($order->getId());
                $estimatedDate->setOrderItemId($item->getItemId());
                $estimatedShippingDate = $this->helper->getEstimatedDateByProduct($product, $quote);
                $estimatedDeliveryDate = $this->getDeliveryDate($quote, $estimatedShippingDate);
                $estimatedDate->setIsDelivery($this->isDelivery);
                $estimatedDate->setDate($estimatedDeliveryDate);
                $this->estimatedDateRepository->save($estimatedDate);
            }
        }

        return $order;
    }

    /**
     * Get Delivery Date
     *
     * @param $quote
     * @param $shippingDate
     * @return mixed
     */
    public function getDeliveryDate($quote, $shippingDate)
    {
        $shippingTimeRules = $this->shippingTimeRuleCollectionFactory->create()
            ->addFieldToSelect('shipping_time_rule_id')
            ->addFieldToFilter('is_active', true)
            ->getData();

        /** Check if rules are matching for current product(s) and cart */
        $hasCartMatched = false;

        for ($i = 0; $i < count($shippingTimeRules) && !$hasCartMatched; $i++) {
            $rule           = $this->shippingTimeRuleRepository->getById($shippingTimeRules[$i]['shipping_time_rule_id']);
            $hasCartMatched = $rule->isQuoteMatching($quote);
            if ($hasCartMatched) {
                $this->isDelivery = true;
                $shippingDate     = $rule->getEstimatedShippingTime($shippingDate);
            }
        }

        return $shippingDate;
    }
}
