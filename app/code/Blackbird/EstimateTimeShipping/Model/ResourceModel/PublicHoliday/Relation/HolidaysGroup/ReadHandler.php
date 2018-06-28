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

use Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday\Relation\HolidaysGroup
 */
class ReadHandler implements ExtensionInterface
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getPublicHolidayId()) {
            $publicHolidaysGroupId = $this->resourcePublicHoliday->lookupHolidaysGroupIds((int)$entity->getPublicHolidayId());
            $entity->setData('holidays_group_id', $publicHolidaysGroupId);
        }
        return $entity;
    }
}
