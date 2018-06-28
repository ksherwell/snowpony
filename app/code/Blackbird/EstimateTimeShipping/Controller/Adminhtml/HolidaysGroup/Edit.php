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

use Blackbird\EstimateTimeShipping\Api\Data\HolidaysGroupInterfaceFactory;
use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\HolidaysGroup
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::holidays_groups';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var HolidaysGroupRepositoryInterface
     */
    protected $holidaysGroupRepository;

    /**
     * @var HolidaysGroupInterfaceFactory
     */
    protected $holidaysGroupFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param HolidaysGroupRepositoryInterface $holidaysGroupRepository
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        HolidaysGroupRepositoryInterface $holidaysGroupRepository,
        HolidaysGroupInterfaceFactory $holidaysGroupInterfaceFactory
    ) {
        $this->resultPageFactory       = $resultPageFactory;
        $this->coreRegistry            = $registry;
        $this->holidaysGroupRepository = $holidaysGroupRepository;
        $this->holidaysGroupFactory    = $holidaysGroupInterfaceFactory;
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
            ->addBreadcrumb(__('Manage Holidays Group'), __('Manage Holidays Group'));
        return $resultPage;
    }

    /**
     * Edit Estimate Time Shipping Holidays Group
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('holidays_group_id');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Holidays Group') : __('New Holidays Group'),
            $id ? __('Edit Holidays Group') : __('New Holidays Group')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Grouping Holidays'));

        // 2. Initial checking
        if ($id) {
            $model = $this->holidaysGroupRepository->getById($id);
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getName());
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This holidays group no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->holidaysGroupFactory->create();
            $resultPage->getConfig()->getTitle()
                ->prepend(__('New Holidays Group'));
        }

        $this->coreRegistry->register('blackbird_ets_holidays_group', $model);

        return $resultPage;
    }
}
