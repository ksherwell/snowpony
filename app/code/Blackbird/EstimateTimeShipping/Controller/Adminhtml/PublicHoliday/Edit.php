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

use Blackbird\EstimateTimeShipping\Model\PublicHolidayFactory;
use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PublicHoliday
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::public_holidays';

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
     * @var PublicHolidayRepositoryInterface
     */
    protected $publicHoliday;

    /**
     * @var
     */
    protected $publicHolidayFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PublicHolidayRepositoryInterface $publicHoliday
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        PublicHolidayRepositoryInterface $publicHoliday,
        PublicHolidayFactory $publicHolidayFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->coreRegistry         = $registry;
        $this->publicHoliday        = $publicHoliday;
        $this->publicHolidayFactory = $publicHolidayFactory;
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
            ->addBreadcrumb(__('Manage Holiday'), __('Manage Holiday'));
        return $resultPage;
    }

    /**
     * Edit Estimate Time Shipping Public Holiday
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('public_holiday_id');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Holiday') : __('New Holiday'),
            $id ? __('Edit Holiday') : __('New Holiday')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Setting Holidays'));

        // 2. Initial checking
        if ($id) {
            $model = $this->publicHoliday->getById($id);

            $resultPage->getConfig()->getTitle()
                ->prepend($model->getName());
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This holiday no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->publicHolidayFactory->create();
            $resultPage->getConfig()->getTitle()
                ->prepend(__('New Holiday'));
        }

        $this->coreRegistry->register('blackbird_ets_public_holiday', $model);

        return $resultPage;
    }
}
