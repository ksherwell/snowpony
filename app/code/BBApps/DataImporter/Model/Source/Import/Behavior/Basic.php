<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model\Source\Import\Behavior;

/**
 * Import behavior source model used for defining the behaviour during the import.
 */
class Basic extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND => __('Add/Update')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'catalog_category';
    }
}
