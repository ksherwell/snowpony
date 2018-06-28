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

namespace Blackbird\EstimateTimeShipping\Controller\Adminhtml\ShippingTimeRule;

use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface;
use Magento\Backend\App\Action;
use Blackbird\EstimateTimeShipping\Model\ShippingTimeRule;

/**
 * Class Delete
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\ShippingTimeRule
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::shipping_time_rules';

    /**
     * @var ShippingTimeRuleRepositoryInterface
     */
    protected $shippingTimeRuleRepository;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
     */
    public function __construct(
        Action\Context $context,
        ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
    ) {
        parent::__construct($context);
        $this->shippingTimeRuleRepository = $shippingTimeRuleRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('shipping_time_rule_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $this->shippingTimeRuleRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccessMessage(__('The shipping time rule has been deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addExceptionMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['shipping_time_rule_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a shipping time rule to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
