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

use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;

/**
 * Class ShippingTimeRule
 * @package Blackbird\EstimateTimeShipping\Model
 */
class ShippingTimeRule extends AbstractModel implements ShippingTimeRuleInterface
{
    /**
     * @var CartRule
     */
    protected $cartRule;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @var ResourceModel\PreparationTimeRule
     */
    protected $shippingTimeRuleResource;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ShippingTimeRule constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CartRuleFactory $rule
     * @param ResourceModel\ShippingTimeRule $shippingTimeRuleResource
     * @param Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        CartRuleFactory $rule,
        \Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule $shippingTimeRuleResource,
        Data $helper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productRepository        = $productRepository;
        $this->cartRule                 = $rule->create();
        $this->shippingTimeRuleResource = $shippingTimeRuleResource;
        $this->helper                   = $helper;
        $this->date                     = $localeDate;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\ShippingTimeRule::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getShippingTime()
    {
        return $this->getData(self::SHIPPING_TIME);
    }

    /**
     * {@inheritDoc}
     */
    public function getHolidaysGroupIds()
    {
        return $this->getData(self::HOLIDAYS_GROUP_IDS);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingDays()
    {
        return $this->getData(self::SHIPPING_DAYS);
    }

    /**
     * {@inheritDoc}
     */
    public function getCartConditionsSerialized()
    {
        return $this->getData(self::CART_CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionsFieldSetId($formName = '', $prefix = '')
    {
        return $formName . $prefix . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function setShippingTime($time)
    {
        return $this->setData(self::SHIPPING_TIME, $time);
    }

    /**
     * {@inheritDoc}
     */
    public function setHolidaysGroupIds($ids)
    {
        return $this->setData(self::HOLIDAYS_GROUP_IDS, $ids);
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingDays($days)
    {
        return $this->setData(self::SHIPPING_DAYS, $days);
    }

    /**
     * Get public holidays group ids
     *
     * @return array
     */
    protected function getGroupIds()
    {
        return $this->shippingTimeRuleResource->lookupHolidaysGroupIds($this->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function getEstimatedShippingTime($actualDayOfWeek = null)
    {
        if ($actualDayOfWeek === null) {
            $actualDayOfWeek = $this->date->date();
        }

        $actualDayOfWeekNumber = $actualDayOfWeek->format('w');
        $shippingDays          = explode(',', $this->getShippingDays());
        $shippingTime          = $this->getShippingTime();
        $publicHolidays        = $this->helper->getAllPublicHolidaysDates($this->getGroupIds());
        $actualShippingTime    = 1;

        /**
         * Check if the current day is a shipping day and current time is before cut of time,
         * if no get the first shipping date after current date
         * Check if the first shipping date is not a public holiday date
         */
        if (!in_array(
            $actualDayOfWeek->format(PublicHolidayInterface::DATE_FORMAT),
            $publicHolidays
        ) && in_array($actualDayOfWeekNumber, $shippingDays)
        ) {
            $firstShippingDay = $actualDayOfWeek;
            $i                = array_search($actualDayOfWeekNumber, $shippingDays);
        } else {
            $firstShippingDay    = $this->helper->getFirstWorkingDay(
                $actualDayOfWeek,
                $actualDayOfWeekNumber,
                $shippingDays,
                $publicHolidays
            );
            $firstShippingDayKey = $firstShippingDay->format('w');
            $i                   = array_search($firstShippingDayKey, $shippingDays);
        }

        $actualDayOfWeek = $firstShippingDay;

        return $this->helper->calculateWorkingTime(
            $actualShippingTime,
            $shippingTime,
            $actualDayOfWeek,
            $shippingDays,
            $publicHolidays,
            $i
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCartRule()
    {
        return $this->cartRule;
    }

    /**
     * {@inheritDoc}
     */
    public function isQuoteMatching($quote)
    {
        $this->getCartRule()->setConditionsSerialized($this->getCartConditionsSerialized());

        return $this->getCartRule()->getConditions()->validate($quote->getShippingAddress());
    }
}
