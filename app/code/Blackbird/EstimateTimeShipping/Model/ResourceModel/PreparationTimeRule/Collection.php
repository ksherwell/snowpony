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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\AbstractCollection;
use Blackbird\EstimateTimeShipping\Model;
use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;

/**
 * Class Collection
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'preparation_time_rule_id';

    /**
     * Define the model and the resource model
     */
    protected function _construct()
    {
        $this->_init(Model\PreparationTimeRule::class, Model\ResourceModel\PreparationTimeRule::class);
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(PreparationTimeRuleInterface::class);
        $this->performAfterLoad(
            'blackbird_ets_preparation_time_rule_holidays_group',
            $entityMetadata->getLinkField(),
            'holidays_group_id'
        );
        $this->performAfterLoad(
            'blackbird_ets_preparation_time_rule_website',
            $entityMetadata->getLinkField(),
            'website_id'
        );

        return parent::_afterLoad();
    }
}
