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
use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PublicHoliday as ResourcePublicHoliday;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PublicHolidayRepository
 * @package Blackbird\EstimateTimeShipping\Model
 */
class PublicHolidayRepository implements PublicHolidayRepositoryInterface
{
    /**
     * @var ResourcePublicHoliday
     */
    protected $resourcePublicHoliday;

    /**
     * @var PublicHolidayFactory
     */
    protected $publicHolidayFactory;

    /**
     * PublicHolidayRepository constructor.
     * @param ResourcePublicHoliday $resourcePublicHoliday
     * @param Data\PublicHolidayInterfaceFactory $publicHolidayFactory
     */
    public function __construct(
        ResourcePublicHoliday $resourcePublicHoliday,
        Data\PublicHolidayInterfaceFactory $publicHolidayFactory
    ) {
        $this->resourcePublicHoliday = $resourcePublicHoliday;
        $this->publicHolidayFactory  = $publicHolidayFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(Data\PublicHolidayInterface $publicHoliday)
    {
        try {
            $this->resourcePublicHoliday->save($publicHoliday);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $publicHoliday;
    }

    /**
     * @inheritDoc
     */
    public function getById($publicHolidayId)
    {
        $publicHoliday = $this->publicHolidayFactory->create();
        $this->resourcePublicHoliday->load($publicHoliday, $publicHolidayId);
        if (!$publicHoliday->getId()) {
            throw new NoSuchEntityException(__('Holiday with id "%1" does not exist.', $publicHolidayId));
        }
        return $publicHoliday;
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
    public function delete(Data\PublicHolidayInterface $publicHoliday)
    {
        try {
            $this->resourcePublicHoliday->delete($publicHoliday);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($publicHolidayId)
    {
        return $this->delete($this->getById($publicHolidayId));
    }
}
