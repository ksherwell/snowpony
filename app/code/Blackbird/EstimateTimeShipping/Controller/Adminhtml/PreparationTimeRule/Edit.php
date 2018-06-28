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

namespace Blackbird\EstimateTimeShipping\Controller\Adminhtml\PreparationTimeRule;

use Blackbird\EstimateTimeShipping\Api\PreparationTimeRuleRepositoryInterface;
use Blackbird\EstimateTimeShipping\Model\PreparationTimeRuleFactory;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PreparationTimeRule
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::preparation_time_rules';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PreparationTimeRuleRepositoryInterface
     */
    protected $preparationTimeRuleRepository;

    /**
     * @var PreparationTimeRuleFactory
     */
    protected $preparationTimeRule;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository,
        PreparationTimeRuleFactory $preparationTimeRule
    ) {
        $this->resultPageFactory             = $resultPageFactory;
        $this->coreRegistry                  = $registry;
        $this->preparationTimeRuleRepository = $preparationTimeRuleRepository;
        $this->preparationTimeRule           = $preparationTimeRule;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Blackbird_EstimateTimeShipping::estimate_time_shipping')
            ->addBreadcrumb(__('Estimate Time Shipping'), __('Estimate Time Shipping'))
            ->addBreadcrumb(__('Manage Preparation Time Rule'), __('Manage Preparation Time Rule'));
        return $resultPage;
    }

    /**
     * Edit Estimate Time Shipping Preparation Time Rule
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('preparation_time_rule_id');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Preparation Time Rule') : __('New Preparation Time Rule'),
            $id ? __('Edit Preparation Time Rule') : __('New Preparation Time Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Preparation Time Rule'));

        // 2. Initial checking
        if ($id) {
            $model = $this->preparationTimeRuleRepository->getById($id);
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getName());

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This preparation time rule no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->preparationTimeRule->create();
            $resultPage->getConfig()->getTitle()
                ->prepend(__('New Preparation Time Rule'));
        }

        $this->coreRegistry->register('blackbird_ets_preparation_time_rule', $model);

        return $resultPage;
    }
}
