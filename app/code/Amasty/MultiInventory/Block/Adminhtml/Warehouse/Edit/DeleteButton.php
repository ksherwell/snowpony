<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
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
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm("' . __('Are you sure you want to do this?')
                        . '", "' . $this->getDeleteUrl() . '")',
                    'sort_order' => 30,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['warehouse_id' => $this->getWarehouse()->getId()]);
    }
}
