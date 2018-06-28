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
 * Estimate Time Shipping PublicHoliday Interface
 * @api
 */
interface PublicHolidayInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID           = 'public_holiday_id';
    const NAME         = 'name';
    const DESCRIPTION  = 'description';
    const RULE_DATE    = 'rule_date';
    const DATE_TYPE    = 'date_type';
    const DATE_PATTERN = '#^([1-9]|0[1-9]|[1-2][0-9]|3[0-1]|[*]{2}|[1-5*]d-[0-6])/(0[1-9]|[1-9]|1[0-2]|[*]{2})/([0-9]{4}|[*]{4})#';
    const DATE_FORMAT  = 'd-m-Y';

    /**
     * Get id
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
     * Get rule date
     *
     * @return string|null
     */
    public function getRuleDate();

    /**
     * Get date type
     *
     * @return int|null
     */
    public function getDateType();

    /**
     * Get array with data for fixed date type
     *
     * @return array
     */
    public function getFixedDateData();

    /**
     * Get array with data for variable date type
     *
     * @return array
     */
    public function getVariableDateData();

    /**
     * Get the real date
     *
     * @return mixed
     */
    public function getRealDate();

    /**
     * Get all holidays group associated
     *
     * @return mixed
     */
    public function getHolidaysGroups();

    /**
     * Set id
     *
     * @param int $id
     * @return PublicHolidayInterface
     */
    public function setId($id);

    /**
     * Set Name
     *
     * @param string $name
     * @return PublicHolidayInterface
     */
    public function setName($name);

    /**
     * Set description
     *
     * @param string $description
     * @return PublicHolidayInterface
     */
    public function setDescription($description);

    /**
     * Set rule date
     *
     * @param string $ruleDate
     * @return PublicHolidayInterface
     */
    public function setRuleDate($ruleDate);

    /**
     * Set date type
     *
     * @param int $dateType
     * @return PublicHolidayInterface
     */
    public function setDateType($dateType);
}
