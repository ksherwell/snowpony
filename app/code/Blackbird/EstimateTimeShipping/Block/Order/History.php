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

use Blackbird\EstimateTimeShipping\Helper\Data;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate;
use Magento\Framework\View\Element\Template;

/**
 * Class History
 * @package Blackbird\EstimateTimeShipping\Block\Order
 */
class History extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var EstimatedDate
     */
    protected $estimatedDateResource;

    /**
     * History constructor.
     * @param Template\Context $context
     * @param Data $helper
     * @param EstimatedDate $estimatedDateResource
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        EstimatedDate $estimatedDateResource,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->estimatedDateResource = $estimatedDateResource;
        parent::__construct($context, $data);
    }

    /**
     * Get estimated order delivery date
     *
     * @return string
     */
    public function getEstimatedDate()
    {
        $orderId         = $this->getRequest()->getParam('order_id');
        $dateInformation = $this->estimatedDateResource->getOrderMaxEstimatedDate($orderId);

        return $this->helper->getEstimatedDateMessage($dateInformation);
    }

    /**
     * Get config for display
     *
     * @return mixed
     */
    public function getHowToDisplay()
    {
        return $this->helper->getHowToDisplay();
    }
}
