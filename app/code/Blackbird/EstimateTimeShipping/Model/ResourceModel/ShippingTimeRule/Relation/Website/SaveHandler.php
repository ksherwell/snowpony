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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Relation\Website;

use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Relation\Website
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

        $oldWebsiteId = $this->resourceShippingTimeRule->lookupWebsiteIds((int)$entity->getShippingTimeRuleId());
        $newWebsiteId = (array)$entity->getWebsites();
        if (empty($newWebsiteId)) {
            $newWebsiteId = (array)$entity->getWebsiteId();
        }

        $table = $this->resourceShippingTimeRule->getTable('blackbird_ets_shipping_time_rule_website');

        $delete = array_diff($oldWebsiteId, $newWebsiteId);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => $entity->getShippingTimeRuleId(),
                'website_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newWebsiteId, $oldWebsiteId);
        if ($insert) {
            $data = [];
            foreach ($insert as $websiteId) {
                $data[] = [
                    $linkField   => $entity->getShippingTimeRuleId(),
                    'website_id' => (int)$websiteId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
