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
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;

/**
 * Public holiday CRUD interface
 * @api
 */
interface PublicHolidayRepositoryInterface
{
    /**
     * Save public holiday
     *
     * @param Data\PublicHolidayInterface $publicHoliday
     * @return PublicHolidayInterface
     */
    public function save(Data\PublicHolidayInterface $publicHoliday);

    /**
     * Retrieve public holiday
     *
     * @param int $publicHolidayId
     * @return PublicHolidayInterface
     */
    public function getById($publicHolidayId);

    /**
     * Retrieve public holidays matching the specific criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return PublicHolidayInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete public holiday
     *
     * @param PublicHolidayInterface $publicHoliday
     * @return bool true on success
     */
    public function delete(Data\PublicHolidayInterface $publicHoliday);

    /**
     * Delete a public holiday rule by ID
     *
     * @param int $publicHolidayId
     * @return bool true on success
     */
    public function deleteById($publicHolidayId);
}
