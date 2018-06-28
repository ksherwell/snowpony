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

use Blackbird\EstimateTimeShipping\Model\CartRule;
use Magento\Backend\App\Action;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class NewConditionHtml
 * @package Blackbird\EstimateTimeShipping\Controller\Adminhtml\ShippingTimeRule
 */
class NewConditionHtml extends Action
{
    /**
     * @var CartRule
     */
    protected $rule;

    /**
     * NewConditionHtml constructor.
     * @param Action\Context $context
     * @param CartRule $rule
     */
    public function __construct(
        Action\Context $context,
        CartRule $rule
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
            ->setPrefix('cart_conditions_serialized')
            ->setData('cart_conditions_serialized', []);

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setFormName($formName);
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
