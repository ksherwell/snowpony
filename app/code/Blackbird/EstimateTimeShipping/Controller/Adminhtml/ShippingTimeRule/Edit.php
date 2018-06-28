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

use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterfaceFactory;
use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\ShippingTimeRule
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::shipping_time_rules';

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
     * @var ShippingTimeRuleRepositoryInterface
     */
    protected $shippingTimeRuleRepository;

    /**
     * @var ShippingTimeRuleInterfaceFactory
     */
    protected $shippingTimeRuleFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
     * @param ShippingTimeRuleInterfaceFactory $shippingTimeRuleInterfaceFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository,
        ShippingTimeRuleInterfaceFactory $shippingTimeRuleInterfaceFactory
    ) {
        $this->resultPageFactory          = $resultPageFactory;
        $this->coreRegistry               = $registry;
        $this->shippingTimeRuleRepository = $shippingTimeRuleRepository;
        $this->shippingTimeRuleFactory    = $shippingTimeRuleInterfaceFactory;
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
            ->addBreadcrumb(__('Manage Shipping Time Rule'), __('Manage Shipping Time Rule'));
        return $resultPage;
    }

    /**
     * Edit Estimate Time Shipping Shipping Time Rule
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('shipping_time_rule_id');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Shipping Time Rule') : __('New Shipping Time Rule'),
            $id ? __('Edit Shipping Time Rule') : __('New Shipping Time Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Time Rule'));

        // 2. Initial checking
        if ($id) {
            $model = $this->shippingTimeRuleRepository->getById($id);
            $model->getCartRule()->setConditionsSerialized($model->getCartConditionsSerialized());
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getName());
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This shipping time rule no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->shippingTimeRuleFactory->create();
            $resultPage->getConfig()->getTitle()
                ->prepend(__('New Shipping Time Rule'));
        }

        $this->coreRegistry->register('blackbird_ets_shipping_time_rule', $model);

        return $resultPage;
    }
}
