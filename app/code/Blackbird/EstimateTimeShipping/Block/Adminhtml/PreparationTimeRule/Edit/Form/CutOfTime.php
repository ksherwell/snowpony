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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\PreparationTimeRule\Edit\Form;

use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;
use Magento\Backend\Block\Template;
use Blackbird\EstimateTimeShipping\Api\PreparationTimeRuleRepositoryInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;

class CutOfTime extends Template
{
    /**
     * Block templates.
     *
     * @var string
     */
    protected $_template = 'Blackbird_EstimateTimeShipping::form/element/cut_of_time.phtml';

    /**
     * @var PreparationTimeRuleRepositoryInterface
     */
    protected $preparationTimeRuleRepository;

    /**
     * @var PreparationTimeRuleInterface | null
     */
    protected $preparationTimeRule = null;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * CutOfTime constructor.
     * @param Template\Context $context
     * @param PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository,
        Data $helper,
        array $data = []
    ) {
        $this->preparationTimeRuleRepository = $preparationTimeRuleRepository;
        $this->helper                        = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Set the preparation time rule id exist
     */
    protected function _construct()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['preparation_time_rule_id'])) {
            $this->preparationTimeRule = $this->preparationTimeRuleRepository->getById($params['preparation_time_rule_id']);
        }
        parent::_construct();
    }

    /**
     * Get array with hours in a day
     *
     * @return array
     */
    public function getHours()
    {
        $hours = [];

        for ($i = 0; $i < 24; $i++) {
            ($i < 10) ? array_push($hours, $this->helper->formatDigit($i)) : array_push($hours, $i);
        }

        return $hours;
    }

    /**
     * Get array with minutes in a hour
     *
     * @return array
     */
    public function getMinutes()
    {
        $minutes = [];

        for ($i = 0; $i < 60; $i++) {
            ($i < 10) ? array_push($minutes, $this->helper->formatDigit($i)) : array_push($minutes, $i);
        }

        return $minutes;
    }

    /**
     * Get the current cut of time hours
     *
     * @return int|null
     */
    public function getEntityHours()
    {
        return ($this->preparationTimeRule != null) ? $this->helper->getHours($this->preparationTimeRule->getCutOfTime()) : null;
    }

    /**
     * Get the current cut of times minutes
     *
     * @return float|null
     */
    public function getEntityMinutes()
    {
        return ($this->preparationTimeRule != null) ? $this->helper->getMinutes($this->preparationTimeRule->getCutOfTime()) : null;
    }
}
