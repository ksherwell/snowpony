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

use Blackbird\EstimateTimeShipping\Api\Data\HolidaysGroupInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class HolidaysGroup
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel
 */
class HolidaysGroup extends AbstractDb
{
    /**
     * @var string
     */
    protected $publicHolidaysGroupTable = '';

    /**
     * @var string
     */
    protected $preparationTimeRuleHolidaysGroupTable = '';

    /**
     * @var string
     */
    protected $shippingTimeRuleHolidaysGroupTable = '';

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * HolidaysGroup constructor.
     * @param Context $context
     * @param MetadataPool $metadata
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadata,
        $connectionName = null
    ) {
        $this->metadataPool = $metadata;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('blackbird_ets_holidays_group', HolidaysGroupInterface::ID);
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $holidaysGroup)
    {
        $where = [HolidaysGroupInterface::ID . ' = ?' => (int)$holidaysGroup->getId()];

        $this->getConnection()->delete($this->getPublicHolidaysGroupTable(), $where);
        $this->getConnection()->delete($this->getPreparationTimeRuleHolidaysGroupTable(), $where);
        $this->getConnection()->delete($this->getShippingTimeRuleHolidaysGroupTable(), $where);

        return parent::_beforeDelete($holidaysGroup);
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

    public function lookupPublicHolidayIds($holidaysGroupId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(HolidaysGroupInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['ptrhg' => $this->getTable('blackbird_ets_public_holidays_group')], 'public_holiday_id')
            ->join(
                ['ptr' => $this->getMainTable()],
                'ptrhg.' . $linkField . ' = ptr.' . $linkField,
                []
            )
            ->where('ptr.' . $entityMetadata->getIdentifierField() . ' = :holidays_group_id');

        return $connection->fetchCol($select, ['holidays_group_id' => (int)$holidaysGroupId]);
    }
}
