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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule\Relation\Website;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule\Relation\Website
 */
class ReadHandler implements ExtensionInterface
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getPreparationTimeRuleId()) {
            $websiteId = $this->resourcePreparationTimeRule->lookupWebsiteIds((int)$entity->getPreparationTimeRuleId());
            $entity->setData('website_id', $websiteId);
        }
        return $entity;
    }
}
