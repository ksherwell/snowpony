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
 * Estimate Time Shipping HolidaysGroup Interface
 * @api
 */
interface HolidaysGroupInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID          = 'holidays_group_id';
    const NAME        = 'name';
    const DESCRIPTION = 'description';

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
     * Get Public Holidays Dates
     *
     * @return mixed
     */
    public function getPublicHolidaysDates();

    /**
     * Set Id
     *
     * @param int $id
     * @return HolidaysGroupInterface
     */
    public function setId($id);

    /**
     * Set Name
     *
     * @param string $name
     * @return HolidaysGroupInterface
     */
    public function setName($name);

    /**
     * Set description
     *
     * @param string $description
     * @return HolidaysGroupInterface
     */
    public function setDescription($description);
}
