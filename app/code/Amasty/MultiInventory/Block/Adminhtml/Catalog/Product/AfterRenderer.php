<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Catalog\Product;

class AfterRenderer extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item
     */
    private $stockResource;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $stockResource,
        \Amasty\MultiInventory\Helper\System $system,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->system = $system;
        $this->stockResource = $stockResource;
        parent::__construct($context, $data);
    }

    public function toHtml()
    {
        if ($this->_request->getParam('isAjax', false)) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return int
     */
    public function isSimple()
    {
        $product = $this->registry->registry('current_product');
        $simple = 0;
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $simple = 1;
        }
        return $simple;
    }

    /**
     * @return string
     */
    public function isEnabled()
    {
        return (int)$this->system->isMultiEnabled();
    }

    /**
     * @return bool
     */
    public function isHaveAssigned()
    {
        $product = $this->registry->registry('current_product');
        return $this->stockResource->getTotalSku(null, $product->getId()) > 1;
    }
}
