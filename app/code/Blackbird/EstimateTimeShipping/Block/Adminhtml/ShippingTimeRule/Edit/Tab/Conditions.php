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

namespace Blackbird\EstimateTimeShipping\Block\Adminhtml\ShippingTimeRule\Edit\Tab;

use Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class Conditions
 * @package Blackbird\EstimateTimeShipping\Block\Adminhtml\ShippingTimeRule\Edit\Tab
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions       = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        if ($this->_coreRegistry->registry('blackbird_ets_shipping_time_rule')) {
            $model    = $this->_coreRegistry->registry('blackbird_ets_shipping_time_rule');
            $formName = 'estimatetimeshipping_shippingtimerule_form';
        } else {
            $model    = $this->_coreRegistry->registry('blackbird_ets_preparation_time_rule');
            $formName = 'estimatetimeshipping_preparationtimerule_form';
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->addTabToForm($model, $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Blackbird\EstimateTimeShipping\Api\Data\ShippingTimeRuleInterface $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $formName, $fieldsetId = 'conditions_fieldset')
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('cartRule_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName, 'cart');
        $newChildUrl          = $this->getUrl(
            'estimatetimeshipping/shippingtimerule/newConditionHtml',
            [
                'form_namespace' => $formName,
                'form'           => $conditionsFieldSetId,
            ]
        );

        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $data = [];
        if ($model) {
            $model->getCartRule()->setConditionsSerialized($model->getData(ShippingTimeRuleInterface::CART_CONDITIONS_SERIALIZED));
            $data = $model->getCartRule()->getData();
        }

        if ($model->getCartRule()->getConditions()->getConditions() === null) {
            $model->getCartRule()->getConditions()->setConditions([]);
        }

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => __('Conditions (don\'t add conditions if rule applies on the whole cart)')]
        )->setRenderer($renderer);

        $fieldset->addField(
            'cart_conditions_serialized',
            'text',
            [
                'name'           => 'cart_conditions_serialized',
                'label'          => __('Conditions'),
                'title'          => __('Conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )
            ->setRule($model->getCartRule())
            ->setRenderer($this->_conditions);

        $form->setValues($data);
        $this->setConditionFormName($model->getCartRule()->getConditions(), $formName);

        return $form;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {

        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
