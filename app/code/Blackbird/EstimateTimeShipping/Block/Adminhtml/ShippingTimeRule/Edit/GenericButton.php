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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\ShippingTimeRule\Edit;

use Magento\Backend\Block\Widget\Context;
use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 * @package Blackbird\EstimateTimeShipping\Block\Adminhtml\ShippingTimeRule\Edit
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ShippingTimeRuleRepositoryInterface
     */
    protected $shippingTimeRuleRepository;

    /**
     * @param Context $context
     * @param ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
     */
    public function __construct(
        Context $context,
        ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
    ) {
        $this->context                    = $context;
        $this->shippingTimeRuleRepository = $shippingTimeRuleRepository;
    }

    /**
     * Return Shipping Time Rule ID
     *
     * @return int|null
     */
    public function getShippingTimeRuleId()
    {
        try {
            return $this->shippingTimeRuleRepository->getById(
                $this->context->getRequest()->getParam('shipping_time_rule_id')
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
