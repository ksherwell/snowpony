<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade the Cms module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->startSetup();
            $this->addQuoteWarehouse($setup);
            $this->addShippingWarehouse($setup);
            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addBackorders($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.4', '<')) {
            $this->correctForeignKey($setup);
        }
    }

    /**
     * Add table for Quote
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addQuoteWarehouse(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable($setup->getTable('amasty_multiinventory_warehouse_quote_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Item ID'
            )
            ->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Quote ID'
            )
            ->addColumn(
                'quote_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Quote Item ID'
            )
            ->addColumn(
                'qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'Qty'
            )
            ->addColumn(
                'warehouse_id',
                Table::TYPE_INTEGER,
                2,
                [],
                'Warehouse Id'
            )
            ->addIndex(
                $setup->getIdxName('amasty_multiinventory_warehouse_quote_item', ['warehouse_id']),
                ['warehouse_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_multiinventory_warehouse_quote_item',
                    'warehouse_id',
                    'amasty_multiinventory_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $setup->getTable('amasty_multiinventory_warehouse'),
                'warehouse_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_multiinventory_warehouse_quote_item',
                    'quote_id',
                    'quote',
                    'entity_id'
                ),
                'quote_id',
                $setup->getTable('quote'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_multiinventory_warehouse_quote_item',
                    'quote_item_id',
                    'quote_item',
                    'item_id'
                ),
                'quote_item_id',
                $setup->getTable('quote_item'),
                'item_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Warehouse Quote Item');

        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addShippingWarehouse(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_multiinventory_warehouse'),
            'is_shipping',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Is Shipping'
            ]
        );
        $table = $setup->getConnection()->newTable($setup->getTable('amasty_multiinventory_warehouse_shipping'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'warehouse_id',
                Table::TYPE_INTEGER,
                2,
                [],
                'Warehouse Id'
            )->addColumn(
                'shipping_method',
                Table::TYPE_TEXT,
                '50',
                ['nullable' => false],
                'Shipping Method'
            )->addColumn(
                'rate',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => true, 'default' => '0.0000'],
                'Rate'
            )->addForeignKey(
                $setup->getFkName(
                    'amasty_multiinventory_warehouse_shipping',
                    'warehouse_id',
                    'amasty_multiinventory_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $setup->getTable('amasty_multiinventory_warehouse'),
                'warehouse_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Warehouse Shipping');

        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * @since 1.3.0 added Backorders and Stock Status
     * @param SchemaSetupInterface $setup
     */
    private function addBackorders(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('amasty_multiinventory_warehouse'),
                \Amasty\MultiInventory\Api\Data\WarehouseInterface::BACKORDERS,
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default'  => \Amasty\MultiInventory\Model\Config\Source\Backorders::USE_CONFIG_OPTION_VALUE,
                    'comment'  => 'Backorders'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('amasty_multiinventory_warehouse_item'),
                \Amasty\MultiInventory\Api\Data\WarehouseItemInterface::BACKORDERS,
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default'  => \Amasty\MultiInventory\Model\Config\Source\Backorders::USE_CONFIG_OPTION_VALUE,
                    'comment'  => 'Backorders'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('amasty_multiinventory_warehouse_item'),
                \Amasty\MultiInventory\Api\Data\WarehouseItemInterface::STOCK_STATUS,
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default'  => \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK,
                    'comment'  => 'Stock status'
                ]
            );
    }

    /**
     * change foreign column for EE
     *
     * @param SchemaSetupInterface $installer
     */
    private function correctForeignKey($installer)
    {
        $installer->getConnection()->dropForeignKey(
            $installer->getTable('amasty_multiinventory_warehouse_item'),
            $installer->getFkName(
                'amasty_multiinventory_warehouse_item',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            )
        );
        //will be added by Recurring

        $installer->getConnection()->dropForeignKey(
            $installer->getTable('amasty_multiinventory_warehouse_import'),
            $installer->getFkName(
                'amasty_multiinventory_warehouse_import',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            )
        );
    }
}
