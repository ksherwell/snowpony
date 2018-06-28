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

use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PublicHoliday
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel
 */
class PublicHoliday extends AbstractDb
{
    /**
     * @var string
     */
    protected $publicHolidaysGroupTable = '';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * PublicHoliday constructor.
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
        $this->_init('blackbird_ets_public_holiday', PublicHolidayInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $holidaysGroup)
    {
        $where = [PublicHolidayInterface::ID . ' = (?)' => (int)$holidaysGroup->getId()];

        $this->getConnection()->delete($this->getPublicHolidaysGroupTable(), $where);

        return parent::_beforeDelete($holidaysGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $dateType = $object->getData('date_type');

        if ($dateType == 0) {
            $object->setData(
                'rule_date',
                $object->getData('fixed_date_day') . "/" . $object->getData('fixed_date_month') . "/" . $object->getData('fixed_date_year')
            );
        } else {
            $object->setData(
                'rule_date',
                $object->getData('variable_date_day_in_month') . "-" . $object->getData('variable_date_day_in_week') . "/" . $object->getData('variable_date_month') . "/" . $object->getData('variable_date_year')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Get public holidays group table name
     *
     * @return string
     */
    public function getPublicHolidaysGroupTable()
    {
        if (empty($this->publicHolidaysGroupTable)) {
            $this->publicHolidaysGroupTable = $this->getTable('blackbird_ets_public_holidays_group');
        }

        return $this->publicHolidaysGroupTable;
    }

    /**
     * Get holidays group ids to which specified item is assigned
     *
     * @param int $publicHolidayId
     * @return array
     */
    public function lookupHolidaysGroupIds($publicHolidayId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PublicHolidayInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['phg' => $this->getTable('blackbird_ets_public_holidays_group')], 'holidays_group_id')
            ->join(
                ['ph' => $this->getMainTable()],
                'phg.' . $linkField . ' = ph.' . $linkField,
                []
            )
            ->where('ph.' . $entityMetadata->getIdentifierField() . ' = :public_holiday_id');

        return $connection->fetchCol($select, ['public_holiday_id' => (int)$publicHolidayId]);
    }

    /**
     * {@inheritDoc}
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
