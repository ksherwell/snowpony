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
use Blackbird\EstimateTimeShipping\Api\Data\PreparationTimeRuleInterfaceFactory;
use Blackbird\EstimateTimeShipping\Model\PreparationTimeRule;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Save
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PreparationTimeRule
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Blackbird_EstimateTimeShipping::preparation_time_rules';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var PreparationTimeRuleRepositoryInterface
     */
    protected $preparationTimeRuleRepository;

    /**
     * @var PreparationTimeRuleInterfaceFactory
     */
    protected $preparationTimeRuleFactory;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository
     * @param PreparationTimeRuleInterfaceFactory $preparationTimeRuleFactory
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        PreparationTimeRuleRepositoryInterface $preparationTimeRuleRepository,
        PreparationTimeRuleInterfaceFactory $preparationTimeRuleFactory,
        Json $serializer
    ) {
        $this->dataPersistor                 = $dataPersistor;
        $this->preparationTimeRuleRepository = $preparationTimeRuleRepository;
        $this->preparationTimeRuleFactory    = $preparationTimeRuleFactory;
        $this->serializer                    = $serializer;
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
            if (empty($data['preparation_time_rule_id'])) {
                $data['preparation_time_rule_id'] = null;
            }
            $id = $this->getRequest()->getParam('preparation_time_rule_id');

            /** @var \Blackbird\EstimateTimeShipping\Model\PreparationTimeRule $preparationTimeRule */
            if ($id) {
                $preparationTimeRule = $this->preparationTimeRuleRepository->getById($id);
            } else {
                $preparationTimeRule = $this->preparationTimeRuleFactory->create();
            }

            $data = $this->prepareConditions($data, $preparationTimeRule);
            $preparationTimeRule->setData($data);

            try {
                $this->preparationTimeRuleRepository->save($preparationTimeRule);
                $this->messageManager->addSuccessMessage(__('You saved the preparation time rule.'));
                $this->dataPersistor->clear('blackbird_ets_preparation_time_rule');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['preparation_time_rule_id' => $preparationTimeRule->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the preparation time rule.')
                );
            }

            $this->dataPersistor->set('blackbird_ets_preparation_time_rule', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['preparation_time_rule_id' => $this->getRequest()->getParam('preparation_time_rule_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     * @param PreparationTimeRule $preparationTimeRule
     * @return array
     */
    protected function prepareConditions(array $data, $preparationTimeRule)
    {
        if (isset($data['rule'], $data['rule']['cart_conditions_serialized'])) {
            $preparationTimeRule->getCartRule()->loadPost($data['rule']);
            $data['cart_conditions_serialized'] = $this->serializer->serialize($preparationTimeRule->getCartRule()->getConditions()->asArray());
        }

        if (isset($data['rule'], $data['rule']['conditions'])) {
            $preparationTimeRule->getCatalogRule()->loadPost($data['rule']);
            $data['catalog_conditions_serialized'] = $this->serializer->serialize($preparationTimeRule->getCatalogRule()->getConditions()->asArray());
        }

        return $data;
    }
}
