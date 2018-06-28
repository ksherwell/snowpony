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

use Blackbird\EstimateTimeShipping\Api\Data\PublicHolidayInterface;
use Magento\Backend\App\Action\Context;
use Blackbird\EstimateTimeShipping\Api\PublicHolidayRepositoryInterface as PublicHolidayRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;

/**
 * Class InlineEdit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PublicHoliday
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::public_holidays';

    /** @var PublicHolidayRepository */
    protected $publicHolidayRepository;

    /** @var JsonFactory */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PublicHolidayRepository $publicHolidayRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PublicHolidayRepository $publicHolidayRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->publicHolidayRepository = $publicHolidayRepository;
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
                foreach (array_keys($postItems) as $publicHolidayId) {
                    /** @var \Blackbird\EstimateTimeShipping\Model\PublicHoliday $publicHoliday */
                    $publicHoliday = $this->publicHolidayRepository->getById($publicHolidayId);
                    try {
                        $publicHoliday->setData(array_merge($publicHoliday->getData(), $postItems[$publicHolidayId]));
                        $this->publicHolidayRepository->save($publicHoliday);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithBlockId(
                            $publicHoliday,
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
     * @param PublicHolidayInterface $publicHoliday
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBlockId(PublicHolidayInterface $publicHoliday, $errorText)
    {
        return '[Public Holiday ID: ' . $publicHoliday->getId() . '] ' . $errorText;
    }
}
