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

use Blackbird\EstimateTimeShipping\Model\CatalogRule;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Backend\App\Action;

/**
 * Class NewConditionHtml
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\PreparationTimeRule
 */
class NewConditionHtml extends Action
{

    /**
     * @var CatalogRule
     */
    protected $rule;

    /**
     * NewConditionHtml constructor.
     * @param Action\Context $context
     * @param CatalogRule $rule
     */
    public function __construct(
        Action\Context $context,
        CatalogRule $rule
    ) {
        $this->rule = $rule;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id       = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr  = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type     = $typeArr[0];

        $model = $this->_objectManager->create($type)
            ->setFormName($formName)
            ->setId($id)
            ->setType($type)
            ->setRule($this->rule)
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
