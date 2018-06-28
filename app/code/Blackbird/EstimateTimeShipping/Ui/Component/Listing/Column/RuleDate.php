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

namespace Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column;

use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class RuleDate
 * @package Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column
 */
class RuleDate extends Column
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * RuleDate constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper            = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $content = '';

        if (!empty($item[$this->getName()])) {
            $origRules = $item['public_holiday_id'];
        }

        if (empty($origRules)) {
            return '';
        }
        if (!is_array($origRules)) {
            $origRules = [$origRules];
        }

        $publicHolidays = $this->getPublicHolidays($origRules);

        foreach ($publicHolidays as $publicHoliday) {
            if ($publicHoliday->getDateType() == 0) {
                $data    = $publicHoliday->getFixedDateData();
                $content .= $this->helper->getDays()[$data[0]] . ' ' . $this->helper->getMonths()[$data[1]] . ' ' . $this->helper->getYears()[$data[2]];
            } else {
                $data    = $publicHoliday->getVariableDateData();
                $content .= $this->helper->getDaysInMonth()[$data[0]] . ' ' . date(
                    'l',
                    mktime(0, 0, 0, 0, $data[1])
                ) . ' of ' . date(
                    'F',
                    mktime(0, 0, 0, $data[2])
                ) . ' ' . $this->helper->getYears()[$data[3]];
            }
        }

        return $content;
    }

    /**
     * @param $publicHolidayId
     * @return \Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday\Collection
     */
    public function getPublicHolidays($publicHolidayId)
    {
        $collection = $this->collectionFactory->create();

        if ((is_array($publicHolidayId) && !empty($publicHolidayId)) || is_numeric($publicHolidayId)) {
            $collection->addFieldToFilter(PublicHolidayInterface::ID, $publicHolidayId);
        }

        return $collection;
    }
}
