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

use Blackbird\EstimateTimeShipping\Api\Data\HolidaysGroupInterface;
use Magento\Backend\App\Action\Context;
use Blackbird\EstimateTimeShipping\Api\HolidaysGroupRepositoryInterface as HolidaysGroupRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;

/**
 * Class InlineEdit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\HolidaysGroup
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::holidays_groups';

    /** @var HolidaysGroupRepository */
    protected $holidaysGroupRepository;

    /** @var JsonFactory */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param HolidaysGroupRepository $holidaysGroupRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        HolidaysGroupRepository $holidaysGroupRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->holidaysGroupRepository = $holidaysGroupRepository;
        $this->jsonFactory             = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error      = false;
        $messages   = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
                $messages[] = __('Please correct the data sent.');
                $error      = true;
            } else {
                foreach (array_keys($postItems) as $holidaysGroupId) {
                    /** @var \Blackbird\EstimateTimeShipping\Model\HolidaysGroup $holidaysGroup */
                    $holidaysGroup = $this->holidaysGroupRepository->getById($holidaysGroupId);
                    try {
                        $holidaysGroup->setData(array_merge($holidaysGroup->getData(), $postItems[$holidaysGroupId]));
                        $this->holidaysGroupRepository->save($holidaysGroup);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithBlockId(
                            $holidaysGroup,
                            __($e->getMessage())
                        );
                        $error      = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => $error
        ]);
    }

    /**
     * Add block title to error message
     *
     * @param HolidaysGroupInterface $holidaysGroup
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBlockId(HolidaysGroupInterface $holidaysGroup, $errorText)
    {
        return '[Holidays Group ID: ' . $holidaysGroup->getId() . '] ' . $errorText;
    }
}
