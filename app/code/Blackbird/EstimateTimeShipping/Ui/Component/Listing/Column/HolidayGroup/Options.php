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

namespace Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column\HolidayGroup;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column\HolidayGroup
 */
class Options implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $holidaysGroupCollection;

    /**
     * Options constructor.
     * @param CollectionFactory $holidaysGroupCollection
     */
    public function __construct(CollectionFactory $holidaysGroupCollection)
    {
        $this->holidaysGroupCollection = $holidaysGroupCollection;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $holidaysGroups = $this->holidaysGroupCollection->create();

        $holidaysGroupsOptions = [];

        foreach ($holidaysGroups as $holidaysGroup) {
            array_push($holidaysGroupsOptions, [
                'value' => $holidaysGroup['holidays_group_id'],
                'label' => $holidaysGroup['name']
            ]);
        }

        return $holidaysGroupsOptions;
    }
}
