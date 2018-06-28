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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\PublicHoliday\Edit\Form;

use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Blackbird\EstimateTimeShipping\Helper\Data;
use Magento\Backend\Block\Template;

/**
 * Class FixedDate
 * @package Blackbird\EstimateTimeShipping\Block\Adminhtml\PublicHoliday\Edit\Form
 */
class FixedDate extends Template
{
    /**
     * Block templates.
     *
     * @var string
     */
    protected $_template = 'Blackbird_EstimateTimeShipping::form/element/fixed_date.phtml';

    /**
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHolidayRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $fixedDate;

    /**
     * @var int
     */
    protected $dateType;

    public function __construct(
        Template\Context $context,
        PublicHolidayRepositoryInterface $publicHolidayRepository,
        Data $helper,
        array $data = []
    ) {
        $this->publicHolidayRepository = $publicHolidayRepository;
        $this->helper                  = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get the public holiday if exist and get information about the fixed date
     */
    protected function _construct()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['public_holiday_id'])) {
            $publicHoliday   = $this->publicHolidayRepository->getById($params['public_holiday_id']);
            $this->fixedDate = $publicHoliday->getFixedDateData();
            $this->dateType  = $publicHoliday->getDateType();
        }
        parent::_construct();
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string|null day of the public holiday fixed date
     */
    public function getEntityDay()
    {
        return ($this->fixedDate != null) ? $this->fixedDate[0] : null;
    }

    /**
     * @return string|null month of the public holiday fixed date
     */
    public function getEntityMonth()
    {
        return ($this->fixedDate != null) ? $this->fixedDate[1] : null;
    }

    /**
     * @return string|null year of the public holiday fixed date
     */
    public function getEntityYear()
    {
        return ($this->fixedDate != null) ? $this->fixedDate[2] : null;
    }

    /**
     * @return int
     */
    public function getDateType()
    {
        return $this->dateType;
    }
}
