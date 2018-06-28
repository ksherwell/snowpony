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
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;

/**
 * Class Success
 * @package Blackbird\EstimateTimeShipping\Block\Order
 */
class Success extends Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var EstimatedDate
     */
    protected $estimatedDateResource;

    /**
     * Success constructor.
     * @param Template\Context $context
     * @param Session $checkoutSession
     * @param Data $helper
     * @param EstimatedDate $estimatedDateResource
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $checkoutSession,
        Data $helper,
        EstimatedDate $estimatedDateResource,
        array $data = []
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->helper                = $helper;
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
        $dateInformation = $this->estimatedDateResource->getOrderMaxEstimatedDate($this->checkoutSession->getLastRealOrder()->getId());

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
