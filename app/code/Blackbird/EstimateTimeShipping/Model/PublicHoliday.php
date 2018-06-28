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
use Blackbird\EstimateTimeShipping\Helper\Data;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PublicHoliday
 * @package Blackbird\EstimateTimeShipping\Model
 */
class PublicHoliday extends AbstractModel implements PublicHolidayInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * PublicHoliday constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->date   = $localeDate;
        $this->helper = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\PublicHoliday::class);
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
     * {@inheritDoc}
     */
    public function getRuleDate()
    {
        return $this->getData(self::RULE_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateType()
    {
        return $this->getData(self::DATE_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function getHolidaysGroups()
    {
        return $this->hasData('holidays_groups') ? $this->getData('holidays_groups') : (array)$this->getData('holidays_group_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
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
    public function setRuleDate($ruleDate)
    {
        return $this->setData(self::RULE_DATE, $ruleDate);
    }

    /**
     * {@inheritDoc}
     */
    public function setDateType($dateType)
    {
        return $this->setData(self::DATE_TYPE, $dateType);
    }

    /**
     * {@inheritDoc}
     */
    public function getFixedDateData()
    {
        return ($this->getDateType() == 0) ? explode('/', $this->getRuleDate()) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getVariableDateData()
    {
        if ($this->getDateType() == 1) {
            $explodedDate = explode('/', $this->getRuleDate());
            $dayRule      = explode('-', $explodedDate[0]);
            unset($explodedDate[0]);
            return array_merge($dayRule, $explodedDate);
        }

        return null;
    }

    /**
     * Get the real date in term of date rule
     *
     * @return \DateTime
     */
    public function getRealDate()
    {
        preg_match(PublicHolidayInterface::DATE_PATTERN, $this->getRuleDate(), $matches);

        $actualDate = $this->date->date();
        list(, $day, $month, $year) = $matches;

        if ($month == '**') {
            $month = $actualDate->format('m');
        }

        if ($year == '****') {
            $year = $actualDate->format('Y');
        }

        if ($day == '**') {
            $day = $this->date->date()->format('d');
        } elseif (!is_numeric($day)) {
            $dayParts = explode('d-', $day);
            $nDay     = $this->helper->getNTimesString($dayParts[0]);
            $weekDay  = $this->helper->getDayByDigit($dayParts[1]);
            $day      = date(
                'd',
                strtotime($nDay . ' ' . $weekDay . ' of ' . $this->helper->getMonthByNumber($month) . ' ' . $year)
            );
        }

        return $this->date->date()->setDate($year, $month, $day);
    }
}
