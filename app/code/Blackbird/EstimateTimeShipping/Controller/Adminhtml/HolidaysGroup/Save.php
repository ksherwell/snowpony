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

namespace Blackbird\EstimateTimeShipping\Controller\Adminhtml\HolidaysGroup;

use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Blackbird\EstimateTimeShipping\Api\Data\HolidaysGroupInterfaceFactory;

/**
 * Class Save
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\HolidaysGroup
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::holidays_groups';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    protected $holidaysGroupRepository;

    protected $holidaysGroupFactory;

    /**
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param HolidaysGroupRepositoryInterface $holidaysGroupRepository
     * @param HolidaysGroupInterfaceFactory $holidaysGroupFactory
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        HolidaysGroupRepositoryInterface $holidaysGroupRepository,
        HolidaysGroupInterfaceFactory $holidaysGroupFactory
    ) {
        $this->dataPersistor           = $dataPersistor;
        $this->holidaysGroupRepository = $holidaysGroupRepository;
        $this->holidaysGroupFactory    = $holidaysGroupFactory;
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
            if (empty($data['holidays_group_id'])) {
                $data['holidays_group_id'] = null;
            }
            $id = $this->getRequest()->getParam('holidays_group_id');

            /** @var \Blackbird\EstimateTimeShipping\Model\PublicHoliday $holidaysGroup */
            if ($id) {
                $holidaysGroup = $this->holidaysGroupRepository->getById($id);
            } else {
                $holidaysGroup = $this->holidaysGroupFactory->create();
            }

            $holidaysGroup->setData($data);

            try {
                $this->holidaysGroupRepository->save($holidaysGroup);
                $this->messageManager->addSuccessMessage(__('You saved the holidays group.'));
                $this->dataPersistor->clear('blackbird_ets_holidays_group');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['holidays_group_id' => $holidaysGroup->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the holidays group.')
                );
            }

            $this->dataPersistor->set('blackbird_ets_holidays_group', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['holidays_group_id' => $this->getRequest()->getParam('holidays_group_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
