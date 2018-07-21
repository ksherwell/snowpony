<?php
/**
 * Widget that adds Olark Live Chat to Magento stores.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@olark.com so we can send you a copy immediately.
 *
 * @category    Olark
 * @package     Olark_Chatbox
 * @copyright   Copyright 2012. Habla, Inc. (http://www.olark.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Olark\Chatbox\Block\Adminhtml\SiteId;

class Comment extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    private $elementFactory;

    /**
     * @var \Olark\Chatbox\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Olark\Chatbox\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Olark\Chatbox\Helper\Data $helper,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text");
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }
        $element->setValue('');
        $afterHtml = $this->helper->getSiteIdDescription();
        $element->setData('after_element_html', $input->getElementHtml() . $afterHtml);
        return $element;
    }
}
