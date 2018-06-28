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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate;

use Blackbird\EstimateTimeShipping\Model\EstimatedDate;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'estimated_date_id';

    /**
     * Define the model and the resource model
     */
    protected function _construct()
    {
        $this->_init(EstimatedDate::class, \Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate::class);
    }
}
