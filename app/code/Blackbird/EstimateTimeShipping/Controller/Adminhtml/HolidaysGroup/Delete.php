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
use Blackbird\EstimateTimeShipping\Model\HolidaysGroup;

/**
 * Class Delete
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\HolidaysGroup
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::holidays_groups';

    /**
     * @var HolidaysGroupRepositoryInterface
     */
    protected $holidaysGroupRepository;

    /**
     * @param Action\Context $context
     * @param HolidaysGroupRepositoryInterface $holidaysGroupRepository
     */
    public function __construct(
        Action\Context $context,
        HolidaysGroupRepositoryInterface $holidaysGroupRepository
    ) {
        parent::__construct($context);
        $this->holidaysGroupRepository = $holidaysGroupRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('holidays_group_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                ;
                $this->holidaysGroupRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccessMessage(__('The holidays group has been deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addExceptionMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['holidays_group_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a holidays group to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
