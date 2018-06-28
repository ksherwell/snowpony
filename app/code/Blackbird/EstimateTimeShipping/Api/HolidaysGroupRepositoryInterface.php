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

use Blackbird\EstimateTimeShipping\Api\Data\HolidaysGroupInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Holidays Group CRUD interface
 * @api
 */
interface HolidaysGroupRepositoryInterface
{
    /**
     * Save holidays group
     *
     * @param Data\HolidaysGroupInterface $holidaysGroup
     * @return HolidaysGroupInterface
     */
    public function save(Data\HolidaysGroupInterface $holidaysGroup);

    /**
     * Retrieve holidays group
     *
     * @param int $holidaysGroupId
     * @return HolidaysGroupInterface
     */
    public function getById($holidaysGroupId);

    /**
     * Retrieve holidays groups matching the specific criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return HolidaysGroupInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete holiday group
     *
     * @param HolidaysGroupInterface $holidaysGroup
     * @return bool true on success
     */
    public function delete(Data\HolidaysGroupInterface $holidaysGroup);

    /**
     * Delete a holidays group by ID
     *
     * @param int $holidaysGroupId
     * @return bool true on success
     */
    public function deleteById($holidaysGroupId);
}
