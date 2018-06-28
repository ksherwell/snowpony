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
 * Estimate Time Shipping ShippingTimeRule Interface
 * @api
 */
interface ShippingTimeRuleInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                         = 'shipping_time_rule_id';
    const NAME                       = 'name';
    const DESCRIPTION                = 'description';
    const IS_ACTIVE                  = 'is_active';
    const SHIPPING_TIME              = 'shipping_time';
    const HOLIDAYS_GROUP_IDS         = 'holidays_group_ids';
    const SHIPPING_DAYS              = 'shipping_days';
    const CART_CONDITIONS_SERIALIZED = 'cart_conditions_serialized';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get is active
     *
     * @return string|null
     */
    public function getIsActive();

    /**
     * Get shipping time
     *
     * @return int|null
     */
    public function getShippingTime();

    /**
     * Get holidays group ids
     *
     * @return string|null
     */
    public function getHolidaysGroupIds();

    /**
     * Get shipping days
     *
     * @return string|null
     */
    public function getShippingDays();

    /**
     * Get cart conditions serialized
     *
     * @return string|null
     */
    public function getCartConditionsSerialized();

    /**
     * @param string $formName
     * @param string $prefix
     * @return mixed
     */
    public function getConditionsFieldSetId($formName = '', $prefix = '');

    /**
     * Calculate the estimated shipping date
     *
     * @param null $actualDayOfWeek
     * @return mixed
     */
    public function getEstimatedShippingTime($actualDayOfWeek = null);

    /**
     * Get the cart rule of shipping time rule
     *
     * @return \Magento\SalesRule\Model\Rule
     */
    public function getCartRule();

    /**
     * Set id
     *
     * @param int $id
     * @return ShippingTimeRuleInterface
     */
    public function setId($id);

    /**
     * Set Name
     *
     * @param string $name
     * @return ShippingTimeRuleInterface
     */
    public function setName($name);

    /**
     * Set description
     *
     * @param string $description
     * @return ShippingTimeRuleInterface
     */
    public function setDescription($description);

    /**
     * Set is active
     *
     * @param string $isActive
     * @return ShippingTimeRuleInterface
     */
    public function setIsActive($isActive);

    /**
     * Set shipping time
     *
     * @param int|string $time
     * @return ShippingTimeRuleInterface
     */
    public function setShippingTime($time);

    /**
     * Set holidays group ids
     *
     * @param int|string|array $ids
     * @return ShippingTimeRuleInterface
     */
    public function setHolidaysGroupIds($ids);

    /**
     * Set shipping days
     *
     * @param int|string|array $days
     * @return ShippingTimeRuleInterface
     */
    public function setShippingDays($days);

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return bool
     */
    public function isQuoteMatching($quote);
}
