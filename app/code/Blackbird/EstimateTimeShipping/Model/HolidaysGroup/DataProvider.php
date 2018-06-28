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

namespace Blackbird\EstimateTimeShipping\Model\HolidaysGroup;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 * @package Blackbird\EstimateTimeShipping\Model\HolidaysGroup
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $holidaysGroupCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $holidaysGroupCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $holidaysGroupCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $publicHoliday \Blackbird\EstimateTimeShipping\Model\HolidaysGroup */
        foreach ($items as $holidaysGroup) {
            $this->loadedData[$holidaysGroup->getId()] = $holidaysGroup->getData();
        }

        $data = $this->dataPersistor->get('blackbird_ets_holidays_group');
        if (!empty($data)) {
            $holidaysGroup = $this->collection->getNewEmptyItem();
            $holidaysGroup->setData($data);
            $this->loadedData[$holidaysGroup->getId()] = $holidaysGroup->getData();
            $this->dataPersistor->clear('blackbird_ets_holidays_group');
        }

        return $this->loadedData;
    }
}
