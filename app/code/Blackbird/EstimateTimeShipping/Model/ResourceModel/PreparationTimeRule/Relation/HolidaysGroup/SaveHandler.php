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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule\Relation\HolidaysGroup;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule;

/**
 * Class SaveHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule\Relation\HolidaysGroup
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var PreparationTimeRule
     */
    protected $resourcePreparationTimeRule;

    /**
     * @param MetadataPool $metadataPool
     * @param PreparationTimeRule $resourcePreparationTimeRule
     */
    public function __construct(
        MetadataPool $metadataPool,
        PreparationTimeRule $resourcePreparationTimeRule
    ) {
        $this->metadataPool                = $metadataPool;
        $this->resourcePreparationTimeRule = $resourcePreparationTimeRule;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(PreparationTimeRuleInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldHolidaysGroup = $this->resourcePreparationTimeRule->lookupHolidaysGroupIds((int)$entity->getPreparationTimeRuleId());
        $newHolidaysGroup = (array)$entity->getHolidaysGroups();
        if (empty($newHolidaysGroup)) {
            $newHolidaysGroup = (array)$entity->getHolidaysGroupId();
        }

        $table = $this->resourcePreparationTimeRule->getTable('blackbird_ets_preparation_time_rule_holidays_group');

        $delete = array_diff($oldHolidaysGroup, $newHolidaysGroup);
        if ($delete) {
            $where = [
                $linkField . ' = ?'        => $entity->getPreparationTimeRuleId(),
                'holidays_group_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newHolidaysGroup, $oldHolidaysGroup);
        if ($insert) {
            $data = [];
            foreach ($insert as $holidaysGroupId) {
                $data[] = [
                    $linkField          => $entity->getPreparationTimeRuleId(),
                    'holidays_group_id' => (int)$holidaysGroupId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
