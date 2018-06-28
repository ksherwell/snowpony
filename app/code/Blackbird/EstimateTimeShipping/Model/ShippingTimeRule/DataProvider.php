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

namespace Blackbird\EstimateTimeShipping\Model\ShippingTimeRule;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 * @package Blackbird\EstimateTimeShipping\Model\ShippingTimeRule
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule\Collection
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
     * @param CollectionFactory $ShippingTimeRuleCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ShippingTimeRuleCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $ShippingTimeRuleCollectionFactory->create();
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
        /** @var $publicHoliday \Blackbird\EstimateTimeShipping\Model\ShippingTimeRule */
        foreach ($items as $shippingTimeRule) {
            $this->loadedData[$shippingTimeRule->getId()] = $shippingTimeRule->getData();
        }

        $data = $this->dataPersistor->get('blackbird_ets_shipping_time_rule');
        if (!empty($data)) {
            $shippingTimeRule = $this->collection->getNewEmptyItem();
            $shippingTimeRule->setData($data);
            $this->loadedData[$shippingTimeRule->getId()] = $shippingTimeRule->getData();
            $this->dataPersistor->clear('blackbird_ets_shipping_time_rule');
        }

        return $this->loadedData;
    }
}
