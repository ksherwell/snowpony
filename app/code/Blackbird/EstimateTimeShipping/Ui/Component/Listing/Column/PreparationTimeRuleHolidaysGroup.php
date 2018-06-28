<?php
/**
 * Blackbird EstimateTimePreparation Module
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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\EntityManager\MetadataPool;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule\CollectionFactory;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule;
use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface;

/**
 * Class PreparationTimeRuleHolidaysGroup
 * @package Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column
 */
class PreparationTimeRuleHolidaysGroup extends Column
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PreparationTimeRule
     */
    protected $resourcePreparationTimeRule;

    /**
     * @var HolidaysGroupRepositoryInterface
     */
    protected $holidaysGroupRepository;

    /**
     * PreparationTimeRuleHolidaysGroup constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param MetadataPool $metadataPool
     * @param CollectionFactory $collectionFactory
     * @param PreparationTimeRule $resource
     * @param HolidaysGroupRepositoryInterface $holidaysGroupRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MetadataPool $metadataPool,
        CollectionFactory $collectionFactory,
        PreparationTimeRule $resource,
        HolidaysGroupRepositoryInterface $holidaysGroupRepository,
        array $components = [],
        array $data = []
    ) {
        $this->metadataPool                = $metadataPool;
        $this->collectionFactory           = $collectionFactory;
        $this->resourcePreparationTimeRule = $resource;
        $this->holidaysGroupRepository     = $holidaysGroupRepository;
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

        $origRules = $item['preparation_time_rule_id'];

        $holidaysGroupIds = $this->resourcePreparationTimeRule->lookupHolidaysGroupIds($origRules);

        foreach ($holidaysGroupIds as $holidaysGroupId) {
            $content .= $this->getHolidaysGroupNameById($holidaysGroupId) . "<br/>";
        }

        return $content;
    }

    /**
     * @param $groupId
     * @return null|string
     */
    public function getHolidaysGroupNameById($groupId)
    {
        return $this->holidaysGroupRepository->getById($groupId)->getName();
    }
}
