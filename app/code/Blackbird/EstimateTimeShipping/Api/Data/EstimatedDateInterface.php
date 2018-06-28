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

namespace Blackbird\EstimateTimeShipping\Api\Data;

/**
 * Estimate Time Shipping Estimated Date Interface
 * @api
 */
interface EstimatedDateInterface
{
    const ID            = 'estimated_date_id';
    const QUOTE_ID      = 'quote_id';
    const QUOTE_ITEM_ID = 'quote_item_id';
    const ORDER_ID      = 'order_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const DATE          = 'date';
    const IS_DELIVERY   = 'is_delivery';

    /**
     * Get Estimated Date Id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Quote Id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Get Quote Item Id
     *
     * @return int
     */
    public function getQuoteItemId();

    /**
     * Get Order Id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Get Order Item Id
     *
     * @return int
     */
    public function getOrderItemId();

    /**
     * Get Estimated Date
     *
     * @return mixed
     */
    public function getDate();

    /**
     * Get if it's a Delivery Date
     *
     * @return mixed
     */
    public function getIsDelivery();

    /**
     * Set Estimated Date Id
     *
     * @param int $id
     * @return mixed
     */
    public function setId($id);

    /**
     * Set Quote Id
     *
     * @param int $quoteId
     * @return mixed
     */
    public function setQuoteId($quoteId);

    /**
     * Set Quote Item Id
     *
     * @param int $quoteItemId
     * @return mixed
     */
    public function setQuoteItemId($quoteItemId);

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * Set Order Item Id
     *
     * @param int $orderItemId
     * @return mixed
     */
    public function setOrderItemId($orderItemId);

    /**
     * Set Estimated Date
     *
     * @param $date
     * @return mixed
     */
    public function setDate($date);

    /**
     * Set if it's a delivery date
     *
     * @param bool $isDelivery
     * @return mixed
     */
    public function setIsDelivery($isDelivery);
}
