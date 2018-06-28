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

namespace Blackbird\EstimateTimeShipping\Controller\Adminhtml\PublicHoliday;

use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterfaceFactory;

/**
 * Class Save
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PublicHoliday
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::public_holidays';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHolidayRepository;

    /**
     * @var PublicHolidayInterfaceFactory
     */
    protected $publicHolidayInterfaceFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PublicHolidayRepositoryInterface $publicHolidayRepository
     * @param PublicHolidayInterfaceFactory $publicHolidayInterfaceFactory
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        PublicHolidayRepositoryInterface $publicHolidayRepository,
        PublicHolidayInterfaceFactory $publicHolidayInterfaceFactory
    ) {
        $this->dataPersistor                 = $dataPersistor;
        $this->publicHolidayRepository       = $publicHolidayRepository;
        $this->publicHolidayInterfaceFactory = $publicHolidayInterfaceFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (empty($data['public_holiday_id'])) {
                $data['public_holiday_id'] = null;
            }
            $id = $this->getRequest()->getParam('public_holiday_id');

            /** @var \Blackbird\EstimateTimeShipping\Model\PublicHoliday $publicHoliday */
            if ($id) {
                $publicHoliday = $this->publicHolidayRepository->getById($id);
            } else {
                $publicHoliday = $this->publicHolidayInterfaceFactory->create();
            }

            $publicHoliday->setData($data);

            try {
                $this->publicHolidayRepository->save($publicHoliday);
                $this->messageManager->addSuccessMessage(__('You saved the holiday.'));
                $this->dataPersistor->clear('blackbird_ets_public_holiday');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['public_holiday_id' => $publicHoliday->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the holiday.')
                );
            }

            $this->dataPersistor->set('blackbird_ets_public_holiday', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['public_holiday_id' => $this->getRequest()->getParam('public_holiday_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
