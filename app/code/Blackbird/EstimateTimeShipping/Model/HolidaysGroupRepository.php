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

use Blackbird\EstimateTimeShipping\Api\Data;
use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup as ResourceHolidaysGroup;

/**
 * Class HolidaysGroupRepository
 * @package Blackbird\EstimateTimeShipping\Model
 */
class HolidaysGroupRepository implements HolidaysGroupRepositoryInterface
{
    /**
     * @var ResourceHolidaysGroup
     */
    protected $resourceHolidaysGroup;

    /**
     * @var HolidaysGroupFactory
     */
    protected $holidaysGroupsFactory;

    /**
     * HolidaysGroupRepository constructor.
     * @param ResourceHolidaysGroup $resourceHolidaysGroup
     * @param Data\HolidaysGroupInterfaceFactory $holidaysGroupsFactory
     */
    public function __construct(
        ResourceHolidaysGroup $resourceHolidaysGroup,
        Data\HolidaysGroupInterfaceFactory $holidaysGroupsFactory
    ) {
        $this->resourceHolidaysGroup = $resourceHolidaysGroup;
        $this->holidaysGroupsFactory = $holidaysGroupsFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(Data\HolidaysGroupInterface $holidaysGroup)
    {
        try {
            $this->resourceHolidaysGroup->save($holidaysGroup);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $holidaysGroup;
    }

    /**
     * @inheritDoc
     */
    public function getById($holidaysGroupId)
    {
        $holidaysGroup = $this->holidaysGroupsFactory->create();
        $this->resourceHolidaysGroup->load($holidaysGroup, $holidaysGroupId);
        if (!$holidaysGroup->getId()) {
            throw new NoSuchEntityException(__('Holidays group with id "%1" does not exist.', $holidaysGroupId));
        }
        return $holidaysGroup;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getList() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(Data\HolidaysGroupInterface $holidaysGroup)
    {
        try {
            $this->resourceHolidaysGroup->delete($holidaysGroup);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($holidaysGroupId)
    {
        return $this->delete($this->getById($holidaysGroupId));
    }
}
