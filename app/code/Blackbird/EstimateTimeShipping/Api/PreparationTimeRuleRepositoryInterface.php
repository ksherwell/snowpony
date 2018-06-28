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

use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Preparation Time Rule CRUD interface
 * @api
 */
interface PreparationTimeRuleRepositoryInterface
{
    /**
     * Save preparation time rule
     *
     * @param Data\PreparationTimeRuleInterface $preparationTimeRule
     * @return PreparationTimeRuleInterface
     */
    public function save(Data\PreparationTimeRuleInterface $preparationTimeRule);

    /**
     * Retrieve preparation time rule
     *
     * @param int $preparationTimeRuleId
     * @return PreparationTimeRuleInterface
     */
    public function getById($preparationTimeRuleId);

    /**
     * Retrieve preparation time rules matching the specific criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return PreparationTimeRuleInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete preparation time rule
     *
     * @param PreparationTimeRuleInterface $preparationTimeRule
     * @return bool true on success
     */
    public function delete(Data\PreparationTimeRuleInterface $preparationTimeRule);

    /**
     * Delete a preparation time rule by ID
     *
     * @param int $preparationTimeRuleId
     * @return bool true on success
     */
    public function deleteById($preparationTimeRuleId);
}
