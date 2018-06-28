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

use Magento\Framework\Api\SearchCriteriaInterface;
use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;

/**
 * Shipping time rule CRUD interface
 * @api
 */
interface ShippingTimeRuleRepositoryInterface
{
    /**
     * Save shipping time rule
     *
     * @param Data\ShippingTimeRuleInterface $shippingTimeRule
     * @return ShippingTimeRuleInterface
     */
    public function save(Data\ShippingTimeRuleInterface $shippingTimeRule);

    /**
     * Retrieve shipping time rule
     *
     * @param int $shippingTimeRuleId
     * @return ShippingTimeRuleInterface
     */
    public function getById($shippingTimeRuleId);

    /**
     * Retrieve shipping time rules matching the specific criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ShippingTimeRuleInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete shipping time rule
     *
     * @param ShippingTimeRuleInterface $shippingTimeRule
     * @return bool true on success
     */
    public function delete(Data\ShippingTimeRuleInterface $shippingTimeRule);

    /**
     * Delete a shipping time rule by ID
     *
     * @param int $shippingTimeRuleId
     * @return bool true on success
     */
    public function deleteById($shippingTimeRuleId);
}
