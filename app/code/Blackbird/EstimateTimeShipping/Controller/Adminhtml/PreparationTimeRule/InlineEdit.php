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

use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterface;
use Magento\Backend\App\Action\Context;
use Blackbird\EstimateTimeShipping\Api\PreparationTimeRuleRepositoryInterface as PreparationTimeRuleRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;

/**
 * Class InlineEdit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PreparationTimeRule
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::preparation_time_rules';

    /** @var PreparationTimeRuleRepository */
    protected $preparationTimeRuleRepository;

    /** @var JsonFactory */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PreparationTimeRuleRepository $preparationTimeRuleRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PreparationTimeRuleRepository $preparationTimeRuleRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->preparationTimeRuleRepository = $preparationTimeRuleRepository;
        $this->jsonFactory                   = $jsonFactory;
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
                foreach (array_keys($postItems) as $preparationTimeRuleId) {
                    /** @var \Blackbird\EstimateTimeShipping\Model\PreparationTimeRule $preparationTimeRule */
                    $preparationTimeRule = $this->preparationTimeRuleRepository->getById($preparationTimeRuleId);
                    try {
                        $preparationTimeRule->setData(array_merge(
                            $preparationTimeRule->getData(),
                            $postItems[$preparationTimeRuleId]
                        ));
                        $this->preparationTimeRuleRepository->save($preparationTimeRule);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithBlockId(
                            $preparationTimeRule,
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
     * @param PreparationTimeRuleInterface $preparationTimeRule
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBlockId(PreparationTimeRuleInterface $preparationTimeRule, $errorText)
    {
        return '[Preparation Time Rule ID: ' . $preparationTimeRule->getId() . '] ' . $errorText;
    }
}
