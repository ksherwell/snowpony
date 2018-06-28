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

use Blackbird\EstimateTimeShipping\Model\CartRule;
use Blackbird\EstimateTimeShipping\Model\CatalogRule;
use Magento\Catalog\Model\Product;

/**
 * Estimate Time Shipping PreparationTimeRule Interface
 * @api
 */
interface PreparationTimeRuleInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                            = 'preparation_time_rule_id';
    const NAME                          = 'name';
    const DESCRIPTION                   = 'description';
    const IS_ACTIVE                     = 'is_active';
    const PREPARATION_TIME              = 'preparation_time';
    const HOLIDAYS_GROUP_IDS            = 'holidays_group_ids';
    const PREPARATION_DAY               = 'preparation_day';
    const CUT_OF_TIME                   = 'cut_of_time';
    const PRIORITY                      = 'priority';
    const CART_CONDITIONS_SERIALIZED    = 'cart_conditions_serialized';
    const CATALOG_CONDITIONS_SERIALIZED = 'catalog_conditions_serialized';

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
     * Get preparation time
     *
     * @return int|null
     */
    public function getPreparationTime();

    /**
     * Get holidays group ids
     *
     * @return string|null
     */
    public function getHolidaysGroupIds();

    /**
     * Get preparation day
     *
     * @return int|null
     */
    public function getPreparationDay();

    /**
     * Get cut of time
     *
     * @return int|null
     */
    public function getCutOfTime();

    /**
     * Get priority
     *
     * @return int|null
     */
    public function getPriority();

    /**
     * Get cart conditions serialized
     *
     * @return mixed
     */
    public function getCartConditionsSerialized();

    /**
     * Get catalog conditions serialized
     *
     * @return mixed
     */
    public function getCatalogConditionsSerialized();

    /**
     * Get the catalog rule
     *
     * @return CatalogRule
     */
    public function getCatalogRule();

    /**
     * Get the cart rule
     *
     * @return CartRule
     */
    public function getCartRule();

    /**
     * Get the estimated preparation time
     *
     * @return mixed
     */
    public function getEstimatedPreparationTime();

    /**
     * @param string $formName
     * @param string $prefix
     * @return mixed
     */
    public function getConditionsFieldSetId($formName = '', $prefix = '');

    /**
     * Get a collection of all product which match with the rule
     *
     * @param null|Product $product
     * @return array
     */
    public function getMatchingProducts($product = null);

    /**
     * Set Id
     *
     * @param int $id
     * @return PreparationTimeRuleInterface
     */
    public function setId($id);

    /**
     * Set Name
     *
     * @param string $name
     * @return PreparationTimeRuleInterface
     */
    public function setName($name);

    /**
     * Set description
     *
     * @param string $description
     * @return PreparationTimeRuleInterface
     */
    public function setDescription($description);

    /**
     * Set is active
     *
     * @param string $isActive
     * @return PreparationTimeRuleInterface
     */
    public function setIsActive($isActive);

    /**
     * Set preparation time
     *
     * @param int $time
     * @return PreparationTimeRuleInterface
     */
    public function setPreparationTime($time);

    /**
     * Set holidays group ids
     *
     * @param int|string|array $ids
     * @return PreparationTimeRuleInterface
     */
    public function setHolidaysGroupIds($ids);

    /**
     * Set preparation day
     *
     * @param int|string $dayNumber
     * @return PreparationTimeRuleInterface
     */
    public function setPreparationDay($dayNumber);

    /**
     * Set cut of time
     *
     * @param int|string $time
     * @return PreparationTimeRuleInterface
     */
    public function setCutOfTime($time);

    /**
     * Set priority
     *
     * @param int $priority
     * @return PreparationTimeRuleInterface
     */
    public function setPriority($priority);

    /**
     * Set cart conditions serialized
     *
     * @param string $conditionsSerialized
     * @return PreparationTimeRuleInterface
     */
    public function setCartConditionsSerialized($conditionsSerialized);

    /**
     * Set catalog conditions serialized
     *
     * @param string $conditionsSerialized
     * @return PreparationTimeRuleInterface
     */
    public function setCatalogConditionsSerialized($conditionsSerialized);

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return bool
     */
    public function isQuoteMatching($quote);
}
