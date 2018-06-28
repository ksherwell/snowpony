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
use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Item
 * @package Blackbird\EstimateTimeShipping\Block\Order
 */
class Item extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $estimatedDateResourceFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Item constructor.
     * @param Template\Context $context
     * @param CollectionFactory $estimatedDateResourceFactory
     * @param TimezoneInterface $timezone
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $estimatedDateResourceFactory,
        TimezoneInterface $timezone,
        Data $helper,
        array $data = []
    ) {
        $this->estimatedDateResourceFactory = $estimatedDateResourceFactory;
        $this->helper                       = $helper;
        $this->timezone                     = $timezone;

        parent::__construct($context, $data);
    }

    /**
     * Get Estimated delivery/shipping date of an item
     *
     * @param $itemId
     * @return \Magento\Framework\Phrase | string
     */
    public function getEstimatedDate($itemId)
    {
        /** @var \Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate\Collection $date */
        $date = $this->estimatedDateResourceFactory->create();
        $date->addFieldToSelect(['date', 'is_delivery'])
            ->addFieldToFilter('quote_item_id', $itemId)
            ->setPageSize(1)
            ->setCurPage(1)
        ;

        $estimatedDate = $date->getFirstItem();
        $formattedDate = $this->timezone->formatDate($estimatedDate->getData('date'), $this->helper->getDateFormat());

        if ($formattedDate !== null) {
            if ($estimatedDate->getData('is_delivery')) {
                return __($this->helper->getProductDeliveryDateMessages(), $formattedDate);
            } else {
                return __($this->helper->getProductShippingDateMessages(), $formattedDate);
            }
        } else {
            if ($this->helper->getDisplayIfNoDate()) {
                return $this->helper->getNoDateMessages();
            } else {
                return '';
            }
        }
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
