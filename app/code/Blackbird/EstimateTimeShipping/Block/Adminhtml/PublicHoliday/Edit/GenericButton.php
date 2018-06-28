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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\PublicHoliday\Edit;

use Magento\Backend\Block\Widget\Context;
use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 * @package Blackbird\EstimateTimeShipping\Block\Adminhtml\PublicHoliday\Edit
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHolidayRepository;

    /**
     * @param Context $context
     * @param PublicHolidayRepositoryInterface $publicHolidayRepository
     */
    public function __construct(
        Context $context,
        PublicHolidayRepositoryInterface $publicHolidayRepository
    ) {
        $this->context                 = $context;
        $this->publicHolidayRepository = $publicHolidayRepository;
    }

    /**
     * Return Public Holiday ID
     *
     * @return int|null
     */
    public function getPublicHolidayId()
    {
        try {
            return $this->publicHolidayRepository->getById(
                $this->context->getRequest()->getParam('public_holiday_id')
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
