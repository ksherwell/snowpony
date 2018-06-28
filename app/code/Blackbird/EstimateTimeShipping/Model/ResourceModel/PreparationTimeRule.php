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

use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\ResourceModel\AbstractResource;

/**
 * Class PreparationTimeRule
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel
 */
class PreparationTimeRule extends AbstractResource
{
    /**
     * @var string
     */
    protected $preparationTimeRuleHolidaysGroupTable = '';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * PreparationTimeRule constructor.
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
        $this->entityManager = $entityManager;
        $this->metadataPool  = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('blackbird_ets_preparation_time_rule', PreparationTimeRuleInterface::ID);
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $preparationTimeRule)
    {
        $where = [PreparationTimeRuleInterface::ID . ' = (?)' => (int)$preparationTimeRule->getId()];

        $this->getConnection()->delete($this->getPreparationTimeRuleHolidaysGroupTable(), $where);
        $this->getConnection()->delete($this->getTable('blackbird_ets_preparation_time_rule_website'), $where);

        return parent::_beforeDelete($preparationTimeRule);
    }

    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setData(
            'cut_of_time',
            $object->getData('cut_of_time_hours') * 60 + $object->getData('cut_of_time_mins')
        );

        $preparationDay = (is_array($object->getData('preparation_day'))) ? implode(
            ',',
            $object->getData('preparation_day')
        ) : $object->getData('preparation_day');
        $object->setData('preparation_day', $preparationDay);
        return parent::_beforeSave($object);
    }

    /**
     * Get preparation time rule holidays group table name
     *
     * @return string
     */
    public function getPreparationTimeRuleHolidaysGroupTable()
    {
        if (empty($this->preparationTimeRuleHolidaysGroupTable)) {
            $this->preparationTimeRuleHolidaysGroupTable = $this->getTable('blackbird_ets_preparation_time_rule_holidays_group');
        }

        return $this->preparationTimeRuleHolidaysGroupTable;
    }

    /**
     * Get holidays group ids to which specified item is assigned
     *
     * @param int $preparationTimeRuleId
     * @return array
     */
    public function lookupHolidaysGroupIds($preparationTimeRuleId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PreparationTimeRuleInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(
                ['ptrhg' => $this->getTable('blackbird_ets_preparation_time_rule_holidays_group')],
                'holidays_group_id'
            )
            ->join(
                ['ptr' => $this->getMainTable()],
                'ptrhg.' . $linkField . ' = ptr.' . $linkField,
                []
            )
            ->where('ptr.' . $entityMetadata->getIdentifierField() . ' = :preparation_time_rule_id');

        return $connection->fetchCol($select, ['preparation_time_rule_id' => (int)$preparationTimeRuleId]);
    }

    public function lookupWebsiteIds($preparationTimeRuleId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PreparationTimeRuleInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['ptrw' => $this->getTable('blackbird_ets_preparation_time_rule_website')], 'website_id')
            ->join(
                ['ptr' => $this->getMainTable()],
                'ptrw.' . $linkField . ' = ptr.' . $linkField,
                []
            )
            ->where('ptr.' . $entityMetadata->getIdentifierField() . ' = :preparation_time_rule_id');

        return $connection->fetchCol($select, ['preparation_time_rule_id' => (int)$preparationTimeRuleId]);
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->_beforeSave($object);
        $this->entityManager->save($object);
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
