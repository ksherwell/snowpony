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

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel;

use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ShippingTimeRule
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel
 */
class ShippingTimeRule extends AbstractDb
{
    /**
     * @var string
     */
    protected $shippingTimeRuleHolidaysGroupTable = '';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * ShippingTimeRule constructor.
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
        $this->metadataPool  = $metadataPool;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('blackbird_ets_shipping_time_rule', ShippingTimeRuleInterface::ID);
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $holidaysGroup)
    {
        $where = [ShippingTimeRuleInterface::ID . ' = (?)' => (int)$holidaysGroup->getId()];

        $this->getConnection()->delete($this->getShippingTimeRuleHolidaysGroupTable(), $where);

        return parent::_beforeDelete($holidaysGroup);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setData('shipping_days', implode(',', $object->getData('shipping_days')));

        return parent::_beforeSave($object);
    }

    /**
     * Get shipping time rule holidays group table name
     *
     * @return string
     */
    public function getShippingTimeRuleHolidaysGroupTable()
    {
        if (empty($this->shippingTimeRuleHolidaysGroupTable)) {
            $this->shippingTimeRuleHolidaysGroupTable = $this->getTable('blackbird_ets_shipping_time_rule_holidays_group');
        }

        return $this->shippingTimeRuleHolidaysGroupTable;
    }

    /**
     * Get holidays group ids to which specified item is assigned
     *
     * @param int $shippingTimeRuleId
     * @return array
     */
    public function lookupHolidaysGroupIds($shippingTimeRuleId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(ShippingTimeRuleInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['strhg' => $this->getTable('blackbird_ets_shipping_time_rule_holidays_group')], 'holidays_group_id')
            ->join(
                ['str' => $this->getMainTable()],
                'strhg.' . $linkField . ' = str.' . $linkField,
                []
            )
            ->where('str.' . $entityMetadata->getIdentifierField() . ' = :shipping_time_rule_id');

        return $connection->fetchCol($select, ['shipping_time_rule_id' => (int)$shippingTimeRuleId]);
    }

    public function lookupWebsiteIds($shippingTimeRuleId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(ShippingTimeRuleInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['strw' => $this->getTable('blackbird_ets_shipping_time_rule_website')], 'website_id')
            ->join(
                ['str' => $this->getMainTable()],
                'strw.' . $linkField . ' = str.' . $linkField,
                []
            )
            ->where('str.' . $entityMetadata->getIdentifierField() . ' = :shipping_time_rule_id');

        return $connection->fetchCol($select, ['shipping_time_rule_id' => (int)$shippingTimeRuleId]);
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->_beforeSave($object);
        $this->entityManager->save($object);
        $this->_afterSave($object);
        return $this;
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $this->entityManager->load($object, $value);
    }

    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entityManager->delete($object);
    }
}
