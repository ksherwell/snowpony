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

/**
 * Class Delete
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PublicHoliday
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::public_holidays';

    /**
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHolidayRepository;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param PublicHolidayRepositoryInterface $publicHolidayRepository
     */
    public function __construct(
        Action\Context $context,
        PublicHolidayRepositoryInterface $publicHolidayRepository
    ) {
        parent::__construct($context);
        $this->publicHolidayRepository = $publicHolidayRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('public_holiday_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $this->publicHolidayRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccessMessage(__('The holiday has been deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addExceptionMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['public_holiday_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a holiday to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
