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

use Blackbird\EstimateTimeShipping\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\CollectionFactory;
use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;

/**
 * Class Day
 * @package Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column
 */
class Day extends Column
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
     * Day constructor.
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
            $origRules = $item['shipping_time_rule_id'];
        }

        if (empty($origRules)) {
            return '';
        }
        if (!is_array($origRules)) {
            $origRules = [$origRules];
        }

        $rules = $this->getRules($origRules);

        $weekDays = $this->helper->getWeekDays();

        foreach ($rules as $rule) {
            $days = explode(',', $rule->getShippingDays());
            foreach ($days as $day) {
                $content .= $weekDays[$day] . "<br/>";
            }
        }

        return $content;
    }

    /**
     * @param $ruleId
     * @return \Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Collection
     */
    public function getRules($ruleId)
    {
        $collection = $this->collectionFactory->create();

        if ((is_array($ruleId) && !empty($ruleId)) || is_numeric($ruleId)) {
            $collection->addFieldToFilter(ShippingTimeRuleInterface::ID, $ruleId);
        }

        return $collection;
    }
}
