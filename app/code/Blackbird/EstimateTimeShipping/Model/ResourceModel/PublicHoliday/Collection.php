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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\AbstractCollection;
use Blackbird\EstimateTimeShipping\Model;
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;

/**
 * Class Collection
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'public_holiday_id';

    /**
     * Define the model and the resource model
     */
    protected function _construct()
    {
        $this->_init(Model\PublicHoliday::class, Model\ResourceModel\PublicHoliday::class);
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(PublicHolidayInterface::class);
        $this->performAfterLoad(
            'blackbird_ets_public_holidays_group',
            $entityMetadata->getLinkField(),
            'holidays_group_id'
        );

        return parent::_afterLoad();
    }
}
