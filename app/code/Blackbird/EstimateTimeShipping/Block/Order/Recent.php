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

namespace Blackbird\EstimateTimeShipping\Block\Order;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Recent
 * @package Blackbird\EstimateTimeShipping\Block\Order
 */
class Recent extends \Magento\Sales\Block\Order\Recent
{
    /**
     * @var EstimatedDate
     */
    protected $estimatedDateResource;

    /**
     * Recent constructor.
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param EstimatedDate $estimatedDateResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        EstimatedDate $estimatedDateResource,
        array $data = []
    ) {
        $this->estimatedDateResource = $estimatedDateResource;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

    /**
     * Get Estimated Delivery/Shipping Date for each order
     */
    protected function _construct()
    {
        parent::_construct();
        foreach ($this->getOrders() as $order) {
            $order->setData(
                'estimated_delivery_date',
                $this->estimatedDateResource->getOrderMaxEstimatedDate($order->getId())['date']
            );
        }
    }
}
