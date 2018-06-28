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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\PreparationTimeRule\Edit;

use Magento\Backend\Block\Widget\Context;
use Blackbird\EstimateTimeShipping\Api\PreparationTimeRuleRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 * @package Blackbird\EstimateTimeShipping\Block\Adminhtml\PreparationTimeRule\Edit
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PreparationTimeRuleRepositoryInterface
     */
    protected $preparationTimeRuleRepository;

    /**
     * @param Context $context
     * @param PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository
     */
    public function __construct(
        Context $context,
        PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository
    ) {
        $this->context                       = $context;
        $this->preparationTimeRuleRepository = $preparationTimeRuleRepository;
    }

    /**
     * Return Preparation Time Rule ID
     *
     * @return int|null
     */
    public function getPreparationTimeRuleId()
    {
        try {
            return $this->preparationTimeRuleRepository->getById(
                $this->context->getRequest()->getParam('preparation_time_rule_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
