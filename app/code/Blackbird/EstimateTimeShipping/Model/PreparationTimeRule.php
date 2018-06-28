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

namespace Blackbird\EstimateTimeShipping\Model;

use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Rule\Model\Condition\Sql\Builder;

/**
 * Class PreparationTimeRule
 * @package Blackbird\EstimateTimeShipping\Model
 */
class PreparationTimeRule extends AbstractModel implements PreparationTimeRuleInterface
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @var ResourceModel\PreparationTimeRule
     */
    protected $preparationTimeRuleResource;

    /**
     * @var CatalogRule
     */
    protected $catalogRule;

    /**
     * @var CartRule
     */
    protected $cartRule;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Builder
     */
    protected $sqlBuilder;

    /**
     * PreparationTimeRule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory
     * @param ResourceModel\PreparationTimeRule $preparationTimeRuleResource
     * @param CartRuleFactory $cartRule
     * @param CatalogRuleFactory $catalogRule
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        ResourceModel\PreparationTimeRule $preparationTimeRuleResource,
        CartRuleFactory $cartRule,
        CatalogRuleFactory $catalogRule,
        Data $helper,
        ProductCollectionFactory $productCollectionFactory,
        Builder $sqlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->sqlBuilder                  = $sqlBuilder;
        $this->productCollectionFactory    = $productCollectionFactory;
        $this->helper                      = $helper;
        $this->date                        = $localeDate;
        $this->combineFactory              = $combineFactory;
        $this->cartRule                    = $cartRule->create();
        $this->catalogRule                 = $catalogRule->create();
        $this->preparationTimeRuleResource = $preparationTimeRuleResource;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\PreparationTimeRule::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreparationTime()
    {
        return $this->getData(self::PREPARATION_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function getHolidaysGroupIds()
    {
        return $this->getData(self::HOLIDAYS_GROUP_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreparationDay()
    {
        return $this->getData(self::PREPARATION_DAY);
    }

    /**
     * {@inheritdoc}
     */
    public function getCutOfTime()
    {
        return $this->getData(self::CUT_OF_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getCartConditionsSerialized()
    {
        return $this->getData(self::CART_CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogConditionsSerialized()
    {
        return $this->getData(self::CATALOG_CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogRule()
    {
        return $this->catalogRule;
    }

    /**
     * {@inheritdoc}
     */
    public function getCartRule()
    {
        return $this->cartRule;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function setPreparationTime($time)
    {
        return $this->setData(self::PREPARATION_TIME, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function setHolidaysGroupIds($ids)
    {
        return $this->setData(self::HOLIDAYS_GROUP_IDS, $ids);
    }

    /**
     * {@inheritdoc}
     */
    public function setPreparationDay($dayNumber)
    {
        return $this->setData(self::PREPARATION_DAY, $dayNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setCutOfTime($time)
    {
        return $this->setData(self::CUT_OF_TIME, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function setCartConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CART_CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * {@inheritdoc}
     */
    public function setCatalogConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CATALOG_CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionsFieldSetId($formName = '', $prefix = '')
    {
        return $formName . $prefix . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsInstance()
    {
        return null;
    }

    /**
     * Get public holidays group ids
     *
     * @return array
     */
    protected function getGroupIds()
    {
        return $this->preparationTimeRuleResource->lookupHolidaysGroupIds($this->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getEstimatedPreparationTime()
    {
        $actualDayOfWeek       = $this->date->date();
        $actualTime            = (int)$actualDayOfWeek->format('H') * 60 + (int)$actualDayOfWeek->format('i');
        $actualDayOfWeekClone  = clone $actualDayOfWeek;
        $actualDayOfWeekNumber = $actualDayOfWeekClone->format('w');
        $preparationTime       = $this->getPreparationTime();
        $preparationDays       = explode(',', $this->getPreparationDay());
        $publicHolidays        = $this->helper->getAllPublicHolidaysDates($this->getGroupIds());
        $actualPreparationTime = 1;

        /**
         * Check if the current day is a preparation day and current time is before cut of time,
         * if no get the first preparation date after current date
         * Check if the first preparation date is not a public holiday date
         */
        if ($actualTime < $this->getCutOfTime() && in_array(
            $actualDayOfWeekNumber,
            $preparationDays
        ) && !in_array(
            $actualDayOfWeek->format(PublicHolidayInterface::DATE_FORMAT),
            $publicHolidays
        )
        ) {
            $firstPreparationDay = $actualDayOfWeek;
            $i                   = array_search($actualDayOfWeekNumber, $preparationDays);
        } else {
            $firstPreparationDay    = $this->helper->getFirstWorkingDay(
                $actualDayOfWeek,
                $actualDayOfWeekNumber,
                $preparationDays,
                $publicHolidays
            );
            $firstPreparationDayKey = $firstPreparationDay->format('w');
            $i                      = array_search($firstPreparationDayKey, $preparationDays);
        }

        $actualDayOfWeek = $firstPreparationDay;

        return $this->helper->calculateWorkingTime(
            $actualPreparationTime,
            $preparationTime,
            $actualDayOfWeek,
            $preparationDays,
            $publicHolidays,
            $i
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingProducts($product = null)
    {
        $this->getCatalogRule()->setConditionsSerialized($this->getCatalogConditionsSerialized());

        return $this->getCatalogRule()->getConditions()->validate($product);
    }

    /**
     * {@inheritDoc}
     */
    public function isQuoteMatching($quote)
    {
        $this->getCartRule()->setConditionsSerialized($this->getCartConditionsSerialized());
        $quote->getShippingAddress()->addData(['total_qty' => $quote->getItemsQty()]);

        return $this->getCartRule()->getConditions()->validate($quote->getShippingAddress());
    }
}
