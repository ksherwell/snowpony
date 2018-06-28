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
use Blackbird\EstimateTimeShipping\Model\ShippingTimeRule;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Save
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\ShippingTimeRule
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::shipping_time_rules';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ShippingTimeRuleRepositoryInterface
     */
    protected $shippingTimeRuleRepository;

    /**
     * @var ShippingTimeRuleInterfaceFactory
     */
    protected $shippingTimeRuleFactory;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository
     * @param ShippingTimeRuleInterfaceFactory $shippingTimeRuleFactory
     * @param Json $serializer
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        ShippingTimeRuleRepositoryInterface $shippingTimeRuleRepository,
        ShippingTimeRuleInterfaceFactory $shippingTimeRuleFactory,
        Json $serializer
    ) {
        $this->dataPersistor              = $dataPersistor;
        $this->shippingTimeRuleRepository = $shippingTimeRuleRepository;
        $this->shippingTimeRuleFactory    = $shippingTimeRuleFactory;
        $this->serializer                 = $serializer;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (empty($data['shipping_time_rule_id'])) {
                $data['shipping_time_rule_id'] = null;
            }
            $id = $this->getRequest()->getParam('shipping_time_rule_id');

            /** @var \Blackbird\EstimateTimeShipping\Model\ShippingTimeRule $shippingTimeRule */
            if ($id) {
                $shippingTimeRule = $this->shippingTimeRuleRepository->getById($id);
            } else {
                $shippingTimeRule = $this->shippingTimeRuleFactory->create();
            }

            $data = $this->prepareConditions($data, $shippingTimeRule);
            $shippingTimeRule->setData($data);

            try {
                $this->shippingTimeRuleRepository->save($shippingTimeRule);
                $this->messageManager->addSuccessMessage(__('You saved the public holiday.'));
                $this->dataPersistor->clear('blackbird_ets_shipping_time_rule');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['shipping_time_rule_id' => $shippingTimeRule->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }

            $this->dataPersistor->set('blackbird_ets_shipping_time_rule', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['shipping_time_rule_id' => $this->getRequest()->getParam('shipping_time_rule_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     * @param ShippingTimeRule $shippingTimeRule
     * @return array
     */
    protected function prepareConditions(array $data, $shippingTimeRule)
    {
        if (isset($data['rule'], $data['rule']['cart_conditions_serialized'])) {
            $shippingTimeRule->getCartRule()->loadPost($data['rule']);
            $data['cart_conditions_serialized'] = $this->serializer->serialize($shippingTimeRule->getCartRule()->getConditions()->asArray());
        }

        return $data;
    }
}
