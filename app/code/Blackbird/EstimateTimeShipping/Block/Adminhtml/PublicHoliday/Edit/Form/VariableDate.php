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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\PublicHoliday\Edit\Form;

use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Config\Model\Config\Source\Locale\Weekdays;

/**
 * Class VariableDate
 * @package Blackbird\EstimateTimeShipping\Block\Adminhtml\PublicHoliday\Edit\Form
 */
class VariableDate extends Template
{
    /**
     * Block templates.
     *
     * @var string
     */
    protected $_template = 'Blackbird_EstimateTimeShipping::form/element/variable_date.phtml';

    /**
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHolidayRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Weekdays
     */
    protected $weekDays;

    /**
     * @var int
     */
    protected $dateType;

    /**
     * @var array
     */
    protected $variableDate;

    /**
     * VariableDate constructor.
     * @param Template\Context $context
     * @param PublicHolidayRepositoryInterface $publicHolidayRepository
     * @param Weekdays $weekDays
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PublicHolidayRepositoryInterface $publicHolidayRepository,
        Weekdays $weekDays,
        Data $helper,
        array $data = []
    ) {
        $this->publicHolidayRepository = $publicHolidayRepository;
        $this->helper                  = $helper;
        $this->weekDays                = $weekDays;
        parent::__construct($context, $data);
    }

    /**
     * Get the public holiday if exist and get information about the variable date
     */
    protected function _construct()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['public_holiday_id'])) {
            $publicHoliday      = $this->publicHolidayRepository->getById($params['public_holiday_id']);
            $this->variableDate = $publicHoliday->getVariableDateData();
            $this->dateType     = $publicHoliday->getDateType();
        }
        parent::_construct();
    }

    /**
     * @return array with each day in a week
     */
    public function getDaysInWeek()
    {
        return $this->weekDays->toOptionArray();
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string|null get the n week day in the month of the public holiday variable date
     */
    public function getEntityDayInMonth()
    {
        return ($this->variableDate != null) ? $this->variableDate[0] : null;
    }

    /**
     * @return string|null get the day of the week of the public holiday variable date
     */
    public function getEntityDayInWeek()
    {
        return ($this->variableDate != null) ? $this->variableDate[1] : null;
    }

    /**
     * @return string|null get the month of the public holiday variable date
     */
    public function getEntityMonth()
    {
        return ($this->variableDate != null) ? $this->variableDate[2] : null;
    }

    /**
     * @return string|null get the year of the public holiday variable date
     */
    public function getEntityYear()
    {
        return ($this->variableDate != null) ? $this->variableDate[3] : null;
    }

    /**
     * @return int the date type value
     */
    public function getDateType()
    {
        return $this->dateType;
    }
}
