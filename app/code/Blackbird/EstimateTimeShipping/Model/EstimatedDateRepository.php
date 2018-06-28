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

namespace Blackbird\EstimateTimeShipping\Model;

use Blackbird\EstimateTimeShipping\Api\Data;
use Blackbird\EstimateTimeShipping\Api\EstimatedDateRepositoryInterface;
use \Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate as EstimatedDateResource;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class EstimatedDateRepository
 * @package Blackbird\EstimateTimeShipping\Model
 */
class EstimatedDateRepository implements EstimatedDateRepositoryInterface
{
    /**
     * @var EstimatedDateResource
     */
    protected $estimatedDateResource;

    /**
     * @var EstimatedDateFactory
     */
    protected $estimatedDateFactory;

    /**
     * EstimatedDateRepository constructor.
     * @param EstimatedDateResource $estimatedDateResource
     * @param EstimatedDateFactory $estimatedDateFactory
     */
    function __construct(
        EstimatedDateResource $estimatedDateResource,
        EstimatedDateFactory $estimatedDateFactory
    ) {
        $this->estimatedDateResource = $estimatedDateResource;
        $this->estimatedDateFactory  = $estimatedDateFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\EstimatedDateInterface $estimatedDate)
    {
        try {
            $this->estimatedDateResource->save($estimatedDate);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $estimatedDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderItemId($orderItemId)
    {
        $estimatedDate = $this->estimatedDateFactory->create();
        $this->estimatedDateResource->load($estimatedDate, $orderItemId, Data\EstimatedDateInterface::ORDER_ITEM_ID);

        return $estimatedDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getByQuoteItemId($quoteItemId)
    {
        $estimatedDate = $this->estimatedDateFactory->create();
        $this->estimatedDateResource->load($estimatedDate, $quoteItemId, Data\EstimatedDateInterface::QUOTE_ITEM_ID);

        return $estimatedDate;
    }
}
