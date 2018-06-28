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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Relation\HolidaysGroup;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Relation\HolidaysGroup
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ShippingTimeRule
     */
    protected $resourceShippingTimeRule;

    /**
     * @param MetadataPool $metadataPool
     * @param ShippingTimeRule $resourceShippingTimeRule
     */
    public function __construct(
        MetadataPool $metadataPool,
        ShippingTimeRule $resourceShippingTimeRule
    ) {
        $this->metadataPool             = $metadataPool;
        $this->resourceShippingTimeRule = $resourceShippingTimeRule;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getShippingTimeRuleId()) {
            $publicHolidaysGroupId = $this->resourceShippingTimeRule->lookupHolidaysGroupIds((int)$entity->getShippingTimeRuleId());
            $entity->setData('holidays_group_id', $publicHolidaysGroupId);
        }
        return $entity;
    }
}
