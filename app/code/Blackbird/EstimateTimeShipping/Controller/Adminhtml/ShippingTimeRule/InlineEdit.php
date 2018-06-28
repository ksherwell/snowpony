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

use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;
use Magento\Backend\App\Action\Context;
use Blackbird\EstimateTimeShipping\Api\ShippingTimeRuleRepositoryInterface as ShippingTimeRuleRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;

/**
 * Class InlineEdit
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\ShippingTimeRule
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::shipping_time_rules';

    /** @var ShippingTimeRuleRepository */
    protected $shippingTimeRuleRepository;

    /** @var JsonFactory */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param ShippingTimeRuleRepository $shippingTimeRuleRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        ShippingTimeRuleRepository $shippingTimeRuleRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->shippingTimeRuleRepository = $shippingTimeRuleRepository;
        $this->jsonFactory                = $jsonFactory;
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
                foreach (array_keys($postItems) as $shippingTimeRuleId) {
                    /** @var \Blackbird\EstimateTimeShipping\Model\ShippingTimeRule $shippingTimeRule */
                    $shippingTimeRule = $this->shippingTimeRuleRepository->getById($shippingTimeRuleId);
                    try {
                        $shippingTimeRule->setData(array_merge(
                            $shippingTimeRule->getData(),
                            $postItems[$shippingTimeRuleId]
                        ));
                        $this->shippingTimeRuleRepository->save($shippingTimeRule);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithBlockId(
                            $shippingTimeRule,
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
     * @param ShippingTimeRuleInterface $shippingTimeRule
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBlockId(ShippingTimeRuleInterface $shippingTimeRule, $errorText)
    {
        return '[Shipping Time Rule ID: ' . $shippingTimeRule->getId() . '] ' . $errorText;
    }
}
