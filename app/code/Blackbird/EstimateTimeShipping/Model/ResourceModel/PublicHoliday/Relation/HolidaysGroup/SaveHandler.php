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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday\Relation\HolidaysGroup;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday;

/**
 * Class SaveHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday\Relation\HolidaysGroup
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var PublicHoliday
     */
    protected $resourcePublicHoliday;

    /**
     * @param MetadataPool $metadataPool
     * @param PublicHoliday $resourcePublicHoliday
     */
    public function __construct(
        MetadataPool $metadataPool,
        PublicHoliday $resourcePublicHoliday
    ) {
        $this->metadataPool          = $metadataPool;
        $this->resourcePublicHoliday = $resourcePublicHoliday;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(PublicHolidayInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldHolidaysGroup = $this->resourcePublicHoliday->lookupHolidaysGroupIds((int)$entity->getPublicHolidayId());
        $newHolidaysGroup = (array)$entity->getHolidaysGroups();
        if (empty($newHolidaysGroup)) {
            $newHolidaysGroup = (array)$entity->getHolidaysGroupId();
        }

        $table = $this->resourcePublicHoliday->getTable('blackbird_ets_public_holidays_group');

        $delete = array_diff($oldHolidaysGroup, $newHolidaysGroup);
        if ($delete) {
            $where = [
                $linkField . ' = ?'        => $entity->getPublicHolidayId(),
                'holidays_group_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newHolidaysGroup, $oldHolidaysGroup);
        if ($insert) {
            $data = [];
            foreach ($insert as $holidaysGroupId) {
                $data[] = [
                    $linkField          => $entity->getPublicHolidayId(),
                    'holidays_group_id' => (int)$holidaysGroupId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
