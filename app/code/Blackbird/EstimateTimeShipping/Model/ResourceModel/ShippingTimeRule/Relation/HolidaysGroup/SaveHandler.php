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

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule;

/**
 * Class SaveHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Relation\HolidaysGroup
 */
class SaveHandler implements ExtensionInterface
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
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(ShippingTimeRuleInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldHolidaysGroup = $this->resourceShippingTimeRule->lookupHolidaysGroupIds((int)$entity->getShippingTimeRuleId());
        $newHolidaysGroup = (array)$entity->getHolidaysGroups();
        if (empty($newHolidaysGroup)) {
            $newHolidaysGroup = (array)$entity->getHolidaysGroupId();
        }

        $table = $this->resourceShippingTimeRule->getTable('blackbird_ets_shipping_time_rule_holidays_group');

        $delete = array_diff($oldHolidaysGroup, $newHolidaysGroup);
        if ($delete) {
            $where = [
                $linkField . ' = ?'        => $entity->getShippingTimeRuleId(),
                'holidays_group_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newHolidaysGroup, $oldHolidaysGroup);
        if ($insert) {
            $data = [];
            foreach ($insert as $holidaysGroupId) {
                $data[] = [
                    $linkField          => $entity->getShippingTimeRuleId(),
                    'holidays_group_id' => (int)$holidaysGroupId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
