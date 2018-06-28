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

namespace Blackbird\EstimateTimeShipping\Observer;

use Blackbird\EstimateTimeShipping\Helper\Data;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AddVarToOrderEmail
 * @package Blackbird\EstimateTimeShipping\Observer
 */
class AddVarToOrderEmail implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var EstimatedDate
     */
    protected $estimatedDateResource;

    /**
     * AddVarToOrderEmail constructor.
     * @param Data $helper
     * @param EstimatedDate $estimatedDateResource
     * @param TimezoneInterface $timezone
     */
    function __construct(
        Data $helper,
        EstimatedDate $estimatedDateResource,
        TimezoneInterface $timezone
    ) {
        $this->helper                = $helper;
        $this->estimatedDateResource = $estimatedDateResource;
        $this->timezone              = $timezone;
    }

    /**
     * Observer to add a variable to Order Confirmation Email to display estimated shipping/delivery date
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->getHowToDisplay()) {
            $transport = $observer->getEvent()->getTransport();
            $order     = $transport['order'];

            $dateInformation = $this->estimatedDateResource->getOrderMaxEstimatedDate($order->getId());

            $transport['estimated_date'] = $this->helper->getEstimatedDateMessage($dateInformation);
        }
    }
}
