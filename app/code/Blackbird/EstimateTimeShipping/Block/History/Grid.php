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

namespace Blackbird\EstimateTimeShipping\Block\History;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data;
use Magento\Sales\Block\Order\View;

/**
 * Class Grid
 * @package Blackbird\EstimateTimeShipping\Block\History
 */
class Grid extends View
{
    /**
     * @var EstimatedDate\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param EstimatedDate\CollectionFactory $collectionFactory
     * @param Data $paymentHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        EstimatedDate\CollectionFactory $collectionFactory,
        Data $paymentHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $httpContext, $paymentHelper, $data);
    }

    /**
     * Get the saved estimated date for an order
     *
     * @return array
     */
    public function getSavedEstimatedDate()
    {
        /** @var EstimatedDate\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('date')
            ->addFieldToFilter('order_id', $this->getOrder()->getId());

        return $collection->getData();
    }
}
