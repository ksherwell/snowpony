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

namespace Blackbird\EstimateTimeShipping\Helper;

use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\EstimatedDate\Collection;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Blackbird\EstimateTimeShipping\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var HolidaysGroupRepositoryInterface
     */
    protected $holidaysGroupRepository;

    /**
     * @var PreparationTimeRule\CollectionFactory
     */
    protected $preparationTimeRuleCollectionFactory;

    /**
     * @var Collection
     */
    protected $resourceCollection;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Data constructor.
     * @param Context $context
     * @param HolidaysGroupRepositoryInterface $holidaysGroupRepository
     * @param PreparationTimeRule\CollectionFactory $preparationTimeRuleCollectionFactory
     * @param TimezoneInterface $timezone
     * @param Collection $resourceCollection
     */
    public function __construct(
        Context $context,
        HolidaysGroupRepositoryInterface $holidaysGroupRepository,
        PreparationTimeRule\CollectionFactory $preparationTimeRuleCollectionFactory,
        TimezoneInterface $timezone,
        Collection $resourceCollection
    ) {
        $this->holidaysGroupRepository              = $holidaysGroupRepository;
        $this->preparationTimeRuleCollectionFactory = $preparationTimeRuleCollectionFactory;
        $this->resourceCollection                   = $resourceCollection;
        $this->timezone                             = $timezone;
        parent::__construct($context);
    }

    /**
     * Get Admin Config for "How to Display Estimated Date"
     *
     * @return mixed
     */
    public function getHowToDisplay()
    {
        return $this->scopeConfig->getValue('ets/general/display_how', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Admin Config to check if we display a message if no estimated date
     *
     * @return mixed
     */
    public function getDisplayIfNoDate()
    {
        return $this->scopeConfig->getValue('ets/general/display_if_not_exist', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Admin Config to check if we display a message if no estimated date
     *
     * @return mixed
     */
    public function getDateFormat()
    {
        return $this->scopeConfig->getValue('ets/general/date_format', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get defined product shipping date message from admin config
     *
     * @return mixed
     */
    public function getProductShippingDateMessages()
    {
        return $this->scopeConfig->getValue('ets/messages/product_shipping_date', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get defined product delivery date message from admin config
     *
     * @return mixed
     */
    public function getProductDeliveryDateMessages()
    {
        return $this->scopeConfig->getValue('ets/messages/product_delivery_date', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get defined order shipping date message from admin config
     *
     * @return mixed
     */
    public function getOrderShippingDateMessages()
    {
        return $this->scopeConfig->getValue('ets/messages/order_shipping_date', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get defined order delivery date message from admin config
     *
     * @return mixed
     */
    public function getOrderDeliveryDateMessages()
    {
        return $this->scopeConfig->getValue('ets/messages/order_delivery_date', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get admin config to display or not the estimated date for order in checkout
     *
     * @return mixed
     */
    public function getOrderCheckoutDisplay()
    {
        return $this->scopeConfig->getValue('ets/display/display_on_checkout', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get admin config to display or not the estimated date under product in checkout
     *
     * @return mixed
     */
    public function getProductCheckoutDisplay()
    {
        return $this->scopeConfig->getValue('ets/display/display_on_checkout_items', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get defined no date message from admin config
     *
     * @return mixed
     */
    public function getNoDateMessages()
    {
        return $this->scopeConfig->getValue('ets/messages/no_date', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param int|null $time
     * @return int|null convert minutes in hours
     */
    public function getHours($time = null)
    {
        if ($time == 0) {
            return $time;
        }

        if ($time != null) {
            return (int)($time / 60);
        } else {
            return null;
        }
    }

    /**
     * @param int|null $time
     * @return float|null convert minutes in minute parts in hour
     */
    public function getMinutes($time = null)
    {
        if ($time == 0) {
            return $time;
        }

        if ($time != null) {
            $cutOfTime = $time / 60;
            return round(($cutOfTime - (int)($cutOfTime)) * 60);
        } else {
            return null;
        }
    }

    /**
     * @return array years
     */
    public function getYears()
    {
        $years = [];
        $maxYear = date('Y') + 10;
        for ($i = 2018; $i <= $maxYear; $i++) {
            $years[$i] = $i;
        }

        $years['****'] = __("Every year");

        return $years;
    }

    /**
     * @return array with each month in a year
     */
    public function getMonths()
    {
        $months = [];

        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i));
        }

        $months['**'] = __('Every Month');

        return $months;
    }

    /**
     * @return array days number in month
     */
    public function getDays()
    {
        $days = [];

        for ($i = 1; $i <= 31; $i++) {
            ($i < 10) ? $days[$i] = $this->formatDigit($i) : $days[$i] = $i;
        }

        $days['**'] = __("Every days");

        return $days;
    }

    /**
     * @return array with each n week day in a month
     */
    public function getDaysInMonth()
    {
        return [
            '1d' => __('First'),
            '2d' => __('Second'),
            '3d' => __('Third'),
            '4d' => __('Fourth'),
            '5d' => __('Last')
        ];
    }

    /**
     * @param int $digit
     * @return string add à 0 before a digit (used for minutes, month number, day number ...)
     */
    public function formatDigit($digit)
    {
        return str_pad($digit, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Transform array of week days from Magento
     *
     * @return array
     */
    public function getWeekDays()
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];
    }

    /**
     * Get the day name by his number in a week (ex: 0 = Sunday, 1 = Monday ...)
     *
     * @param int $digit number of the day in a week
     * @return mixed
     */
    public function getDayByDigit($digit)
    {
        return $this->getWeekDays()[$digit];
    }

    /**
     * Get label of the n days (for strtotime)
     *
     * @param $n
     * @return mixed
     */
    public function getNTimesString($n)
    {
        $times = [
            1 => 'First',
            2 => 'Second',
            3 => 'Third',
            4 => 'Fourth',
            5 => 'Last'
        ];

        if (!isset($times[$n])) {
            throw new \InvalidArgumentException(__('The argument must be an integer between "1" and "5".'));
        }

        return $times[$n];
    }

    /**
     * Get month name by month number
     *
     * @param $monthNumber
     * @return false|string
     */
    public function getMonthByNumber($monthNumber)
    {
        return date('F', mktime(0, 0, 0, $monthNumber));
    }

    /**
     * Get all days which is in the given holiday group
     *
     * @param array $groupIds
     * @return array
     */
    public function getAllPublicHolidaysDates($groupIds)
    {
        $dates = [];

        foreach ($groupIds as $groupId) {
            $holidaysGroup = $this->holidaysGroupRepository->getById($groupId);
            $dates         = array_merge($dates, $holidaysGroup->getPublicHolidaysDates());
        }

        return $dates;
    }

    /**
     * Get next working day in terms after a given day for preparation or shipping days
     *
     * @param int $dayNumber
     * @param array $workingDays
     * @return int
     */
    public function getNextWorkingDay($dayNumber, $workingDays)
    {
        $daysValue = array_search($dayNumber, $workingDays);

        if ($daysValue !== false) {
            return ($daysValue + 1 < count($workingDays)) ? $workingDays[$daysValue + 1] : $workingDays[0];
        } else {
            foreach ($workingDays as $day) {
                if ($day > $dayNumber) {
                    return $day;
                }
            }
        }

        return 0;
    }

    /**
     * Get each next preparation/shipping day up to the last preparation day until have
     * the last preparation/shipping day
     *
     * @param $actualWorkingTime
     * @param $workingTime
     * @param \DateTime $actualDayOfWeek
     * @param $workingDays
     * @param $publicHolidays
     * @param $dayId
     * @return mixed
     */
    public function calculateWorkingTime(
        $actualWorkingTime,
        $workingTime,
        $actualDayOfWeek,
        $workingDays,
        $publicHolidays,
        $dayId
    ) {
        $workingDaysNumber = count($workingDays);
        while ($actualWorkingTime < $workingTime) {
            $nextPreparationDay = $actualDayOfWeek->modify('next ' . $this->getDayByDigit($this->getNextWorkingDay(
                $workingDays[$dayId],
                $workingDays
            )));

            if (!in_array($nextPreparationDay->format(PublicHolidayInterface::DATE_FORMAT), $publicHolidays)) {
                $actualWorkingTime++;
            }

            $actualDayOfWeek = $nextPreparationDay;
            $dayId           = ($dayId + 1 < $workingDaysNumber) ? $dayId + 1 : 0;
        }

        return $actualDayOfWeek;
    }

    /**
     * Get first preparation/shipping day in terms of public holidays
     *
     * @param \DateTime $actualDayOfWeek
     * @param $actualDayOfWeekNumber
     * @param $workingDays
     * @param $publicHolidays
     * @return mixed
     */
    public function getFirstWorkingDay($actualDayOfWeek, $actualDayOfWeekNumber, $workingDays, array $publicHolidays)
    {
        $firstShippingDay = $actualDayOfWeek->modify('next ' . $this->getDayByDigit($this->getNextWorkingDay(
            $actualDayOfWeekNumber,
            $workingDays
        )));
        while (in_array($firstShippingDay->format(PublicHolidayInterface::DATE_FORMAT), $publicHolidays)) {
            $firstShippingDay->modify('next ' . $this->getDayByDigit($this->getNextWorkingDay(
                $firstShippingDay->format('w'),
                $workingDays
            )));
        }

        return $firstShippingDay;
    }

    /**
     * Get estimated shipping date for a given product
     *
     * @param Product|mixed $product
     * @param Quote $quote
     * @return mixed
     */
    public function getEstimatedDateByProduct($product, $quote)
    {
        $preparationTimeRules = $this->preparationTimeRuleCollectionFactory->create()
            ->addFieldToFilter('is_active', true)
            ->getIterator();

        $date = null;
        $priority = -1;

        for ($iterator = $preparationTimeRules; $iterator->valid(); $iterator->next()) {
            /** @var \Blackbird\EstimateTimeShipping\Model\PreparationTimeRule $rule */
            $rule              = $iterator->current();
            $rulePriority      = $rule->getPriority();
            $hasCatalogMatched = $rule->getMatchingProducts($product);
            $hasCartMatched    = $rule->isQuoteMatching($quote);
            if ($hasCatalogMatched && $hasCartMatched && $rulePriority > $priority) {
                $date = $rule->getEstimatedPreparationTime();
                $priority = $rulePriority;
            }
        }

        return $date;
    }

    /**
     * Get the good message for the estimated delivery/shipping date
     *
     * @param $dateInformation
     * @return \Magento\Framework\Phrase | string
     */
    public function getEstimatedDateMessage($dateInformation)
    {
        $date = $dateInformation['date'];

        if ($date !== null) {
            $formattedDate = $this->timezone->formatDate(
                $dateInformation['date'],
                $this->getDateFormat()
            );

            if ($dateInformation['is_delivery']) {
                return __($this->getOrderDeliveryDateMessages(), $formattedDate);
            } else {
                return __($this->getOrderShippingDateMessages(), $formattedDate);
            }
        } else {
            if ($this->getDisplayIfNoDate()) {
                return $this->getNoDateMessages();
            } else {
                return '';
            }
        }
    }

    /**
     * Return the label of boolean status
     *
     * @param $isActive
     * @return \Magento\Framework\Phrase
     */
    public function getStatusLabel($isActive)
    {
        switch ($isActive) {
            case 0:
                return __('Inactive');
            default:
                return __('Active');
        }
    }
}
