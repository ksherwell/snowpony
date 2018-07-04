<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Import;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->update('save', 'id', 'upload_button');
        $this->buttonList->update('save', 'onclick', 'varienImport.parse();');
        $this->buttonList->update('save', 'data_attribute', '');
        $this->buttonList->add('to_grid', [
            'label' => 'To Grid',
            'onclick' => 'varienImport.next()',
            'data_attribute' => '',
            'class' => 'primary',
            'style' => 'display:none'
        ]);
        $this->_objectId = 'item_id';
        $this->_blockGroup = 'Amasty_MultiInventory';
        $this->_controller = 'adminhtml_import';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Import');
    }
}
