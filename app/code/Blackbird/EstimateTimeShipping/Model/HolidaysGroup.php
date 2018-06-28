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

use Blackbird\EstimateTimeShipping\Api\Data\HolidaysGroupInterface;
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;

/**
 * Class HolidaysGroup
 * @package Blackbird\EstimateTimeShipping\Model
 */
class HolidaysGroup extends AbstractModel implements HolidaysGroupInterface
{
    /**
     * @var ResourceModel\HolidaysGroup
     */
    protected $holidaysGroupResource;

    /**
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHolidaysRepository;

    /**
     * HolidaysGroup constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\HolidaysGroup $holidaysGroupResource
     * @param PublicHolidayRepositoryInterface $publicHolidayRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Blackbird\EstimateTimeShipping\Model\ResourceModel\HolidaysGroup $holidaysGroupResource,
        PublicHolidayRepositoryInterface $publicHolidayRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->holidaysGroupResource    = $holidaysGroupResource;
        $this->publicHolidaysRepository = $publicHolidayRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\HolidaysGroup::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicHolidaysDates()
    {
        $publicHolidaysIds = $this->holidaysGroupResource->lookupPublicHolidayIds($this->getId());

        $dates = [];

        foreach ($publicHolidaysIds as $publicHolidaysId) {
            $publicHoliday = $this->publicHolidaysRepository->getById($publicHolidaysId);
            array_push($dates, $publicHoliday->getRealDate()->format(PublicHolidayInterface::DATE_FORMAT));
        }

        return $dates;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }
}
