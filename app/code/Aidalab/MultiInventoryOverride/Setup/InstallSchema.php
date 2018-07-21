<?php

namespace Aidalab\MultiInventoryOverride\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Create new column 'wh_delivery_postcodes' in table 'amasty_multiinventory_warehouse'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_multiinventory_warehouse'),
            'wh_delivery_postcodes',
            ['type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Warehouse Delivery Postcodes']
        );

        $setup->endSetup();
    }
}
