<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ResetButton
 */
class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getWarehouse()) {
            if (!$this->getWarehouse()->getIsGeneral()) {
                $data = [
                    'label' => __('Duplicate'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm("' . __('Are you sure you want to do this?')
                        . '", "' . $this->getDuplicateUrl() . '")',
                    'sort_order' => 20,
                ];
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', ['warehouse_id' => $this->getWarehouse()->getId()]);
    }
}
