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

namespace Blackbird\EstimateTimeShipping\Api;

/**
 * Estimated Date CRUD interface
 * @api
 */
interface EstimatedDateRepositoryInterface
{
    /**
     * Save Estimated Date
     *
     * @param Data\EstimatedDateInterface $estimatedDate
     * @return Data\EstimatedDateInterface
     */
    public function save(Data\EstimatedDateInterface $estimatedDate);

    /**
     * Get Estimated Date by Order Item Id
     *
     * @param int $orderItemId
     * @return Data\EstimatedDateInterface
     */
    public function getByOrderItemId($orderItemId);

    /**
     * Get Estimated Date by Quote Item Id
     *
     * @param int $quoteItemId
     * @return Data\EstimatedDateInterface
     */
    public function getByQuoteItemId($quoteItemId);
}
