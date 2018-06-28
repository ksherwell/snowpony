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

namespace Blackbird\EstimateTimeShipping\Plugin\History;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate;
use Magento\Sales\Block\Order\History;

/**
 * Class OrderManagement
 * @package Blackbird\EstimateTimeShipping\Plugin\History
 */
class OrderManagement
{
    /**
     * @var EstimatedDate
     */
    private $estimatedDateResource;

    /**
     * OrderManagement constructor.
     * @param EstimatedDate $estimatedDateResource
     */
    function __construct(
        EstimatedDate $estimatedDateResource
    ) {
        $this->estimatedDateResource = $estimatedDateResource;
    }

    /**
     * @param History $subject
     * @param callable $proceed
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function aroundGetOrders(History $subject, callable $proceed)
    {
        /** @var bool|\Magento\Sales\Model\ResourceModel\Order\Collection $orders */
        $orders = $proceed();

        foreach ($orders as $order) {
            $order->setData(
                'estimated_delivery_date',
                $this->estimatedDateResource->getOrderMaxEstimatedDate($order->getId())['date']
            );
        }

        return $orders;
    }
}
