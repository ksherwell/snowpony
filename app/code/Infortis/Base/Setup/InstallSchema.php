<?php

namespace Infortis\Base\Setup;
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
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'package_address_type',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'package_address_type',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'package_address',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'package_address',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'package_address_type',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'package_address_type',
            ]
        );
		
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'package_address',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'package_address',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'package_address_type',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'package_address_type',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'package_address',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'package_address',
            ]
        );

        $setup->endSetup();
    }
}