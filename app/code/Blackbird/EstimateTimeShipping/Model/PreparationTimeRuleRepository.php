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
use Blackbird\EstimateTimeShipping\Api\PreparationTimeRuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\PreparationTimeRule as ResourcePreparationTimeRule;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PreparationTimeRuleRepository
 * @package Blackbird\EstimateTimeShipping\Model
 */
class PreparationTimeRuleRepository implements PreparationTimeRuleRepositoryInterface
{
    /**
     * @var ResourcePreparationTimeRule
     */
    protected $resourcePreparationTimeRule;

    /**
     * @var PreparationTimeRuleFactory
     */
    protected $preparationTimeRuleFactory;

    /**
     * PreparationTimeRuleRepository constructor.
     * @param ResourcePreparationTimeRule $resourcePreparationTimeRule
     * @param Data\PreparationTimeRuleInterfaceFactory $preparationTimeRuleFactory
     */
    public function __construct(
        ResourcePreparationTimeRule $resourcePreparationTimeRule,
        Data\PreparationTimeRuleInterfaceFactory $preparationTimeRuleFactory
    ) {
        $this->resourcePreparationTimeRule = $resourcePreparationTimeRule;
        $this->preparationTimeRuleFactory  = $preparationTimeRuleFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(Data\PreparationTimeRuleInterface $preparationTimeRule)
    {
        try {
            $this->resourcePreparationTimeRule->save($preparationTimeRule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $preparationTimeRule;
    }

    /**
     * @inheritDoc
     */
    public function getById($preparationTimeRuleId)
    {
        $preparationTimeRule = $this->preparationTimeRuleFactory->create();
        $this->resourcePreparationTimeRule->load($preparationTimeRule, $preparationTimeRuleId);
        if (!$preparationTimeRule->getId()) {
            throw new NoSuchEntityException(__(
                'Preparation time rule with id "%1" does not exist.',
                $preparationTimeRuleId
            ));
        }
        return $preparationTimeRule;
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
    public function delete(Data\PreparationTimeRuleInterface $preparationTimeRule)
    {
        try {
            $this->resourcePreparationTimeRule->delete($preparationTimeRule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($preparationTimeRuleId)
    {
        return $this->delete($this->getById($preparationTimeRuleId));
    }
}
