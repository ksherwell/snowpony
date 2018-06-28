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
use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule as ResourceShippingTimeRule;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ShippingTimeRuleRepository
 * @package Blackbird\EstimateTimeShipping\Model
 */
class ShippingTimeRuleRepository implements ShippingTimeRuleRepositoryInterface
{
    /**
     * @var ResourceShippingTimeRule
     */
    protected $resourceShippingTimeRule;

    /**
     * @var ShippingTimeRuleFactory
     */
    protected $shippingTimeRuleFactory;

    /**
     * ShippingTimeRuleRepository constructor.
     * @param ResourceShippingTimeRule $resourceShippingTimeRule
     * @param Data\ShippingTimeRuleInterfaceFactory $shippingTimeRuleFactory
     */
    public function __construct(
        ResourceShippingTimeRule $resourceShippingTimeRule,
        Data\ShippingTimeRuleInterfaceFactory $shippingTimeRuleFactory
    ) {
        $this->resourceShippingTimeRule = $resourceShippingTimeRule;
        $this->shippingTimeRuleFactory  = $shippingTimeRuleFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(Data\ShippingTimeRuleInterface $shippingTimeRule)
    {
        try {
            $this->resourceShippingTimeRule->save($shippingTimeRule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $shippingTimeRule;
    }

    /**
     * @inheritDoc
     */
    public function getById($shippingTimeRuleId)
    {
        $shippingTimeRule = $this->shippingTimeRuleFactory->create();
        $this->resourceShippingTimeRule->load($shippingTimeRule, $shippingTimeRuleId);
        if (!$shippingTimeRule->getId()) {
            throw new NoSuchEntityException(__('Shipping time rule with id "%1" does not exist.', $shippingTimeRuleId));
        }
        return $shippingTimeRule;
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
    public function delete(Data\ShippingTimeRuleInterface $shippingTimeRule)
    {
        try {
            $this->resourceShippingTimeRule->delete($shippingTimeRule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($shippingTimeRuleId)
    {
        return $this->delete($this->getById($shippingTimeRuleId));
    }
}
