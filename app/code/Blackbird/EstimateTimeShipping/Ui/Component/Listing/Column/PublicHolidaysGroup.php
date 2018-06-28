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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\EntityManager\MetadataPool;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday\CollectionFactory;
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday;
use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface;

/**
 * Class PublicHolidaysGroup
 * @package Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column
 */
class PublicHolidaysGroup extends Column
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
     * @var PublicHoliday
     */
    protected $resourcePublicHoliday;

    /**
     * @var HolidaysGroupRepositoryInterface
     */
    protected $holidaysGroupRepository;

    /**
     * PublicHolidaysGroup constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param MetadataPool $metadataPool
     * @param CollectionFactory $collectionFactory
     * @param PublicHoliday $resource
     * @param HolidaysGroupRepositoryInterface $holidaysGroupRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MetadataPool $metadataPool,
        CollectionFactory $collectionFactory,
        PublicHoliday $resource,
        HolidaysGroupRepositoryInterface $holidaysGroupRepository,
        array $components = [],
        array $data = []
    ) {
        $this->metadataPool            = $metadataPool;
        $this->collectionFactory       = $collectionFactory;
        $this->resourcePublicHoliday   = $resource;
        $this->holidaysGroupRepository = $holidaysGroupRepository;
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

        $origRules = $item['public_holiday_id'];

        $holidaysGroupIds = $this->getHolidaysGroupIds($origRules);

        foreach ($holidaysGroupIds as $holidaysGroupId) {
            $content .= $this->getHolidaysGroupNameById($holidaysGroupId) . "<br/>";
        }

        return $content;
    }

    /**
     * @param $ruleId
     * @return array
     */
    public function getHolidaysGroupIds($ruleId)
    {
        $connection = $this->resourcePublicHoliday->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(PublicHolidayInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(
                ['strhg' => $this->resourcePublicHoliday->getTable('blackbird_ets_public_holidays_group')],
                'holidays_group_id'
            )
            ->join(
                ['str' => $this->resourcePublicHoliday->getMainTable()],
                'strhg.' . $linkField . ' = str.' . $linkField,
                []
            )
            ->where('str.' . $entityMetadata->getIdentifierField() . ' = :public_holiday_id');

        return $connection->fetchCol($select, ['public_holiday_id' => (int)$ruleId]);
    }

    public function getHolidaysGroupNameById($groupId)
    {
        return $this->holidaysGroupRepository->getById($groupId)->getName();
    }
}
