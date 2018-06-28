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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Blackbird\EstimateTimeShipping\Model;

/**
 * Class Collection
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'holidays_group_id';

    /**
     * Define the model and the resource model
     */
    protected function _construct()
    {
        $this->_init(Model\HolidaysGroup::class, Model\ResourceModel\HolidaysGroup::class);
    }
}
