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
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://store.bird.eu/license/
 * @support         help@bird.eu
 */

namespace Blackbird\EstimateTimeShipping\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
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
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'blackbird_ets_holidays_group'
         */
        if (!$installer->tableExists('blackbird_ets_holidays_group')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_holidays_group'))
                ->addColumn(
                    'holidays_group_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'auto_increment' => true,
                        'primary' => true,
                    ],
                    'Holidays Group Id'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Name'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Description'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_holidays_group',
                        [
                            'name',
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'name',
                    ],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Estimate Time Shipping Holidays Group');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_preparation_time_rule'
         */
        if (!$installer->tableExists('blackbird_ets_preparation_time_rule')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_preparation_time_rule'))
                ->addColumn(
                    'preparation_time_rule_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'auto_increment' => true,
                        'primary' => true,
                    ],
                    'Preparation Time Rule Id'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Name'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Description'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Is Active'
                )
                ->addColumn(
                    'preparation_time',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Preparation Time'
                )
                ->addColumn(
                    'preparation_day',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Preparation Day'
                )
                ->addColumn(
                    'cut_of_time',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Cut Of Time'
                )
                ->addColumn(
                    'priority',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Priority'
                )
                ->addColumn(
                    'cart_conditions_serialized',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Cart Conditions Serialized'
                )
                ->addColumn(
                    'catalog_conditions_serialized',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Catalog Conditions Serialized'
                )
                ->setComment('Estimate Time Shipping Preparation Time Rule');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_preparation_time_rule_holidays_group'
         */
        if (!$installer->tableExists('blackbird_ets_preparation_time_rule_holidays_group')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_preparation_time_rule_holidays_group'))
                ->addColumn(
                    'holidays_group_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Holidays Group Id'
                )
                ->addColumn(
                    'preparation_time_rule_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Preparation Time Rule Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_preparation_time_rule_holidays_group',
                        [
                            'preparation_time_rule_id',
                        ],
                        null
                    ),
                    [
                        'preparation_time_rule_id',
                    ],
                    []
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_preparation_time_rule_holidays_group',
                        'holidays_group_id',
                        'blackbird_ets_holidays_group',
                        'holidays_group_id'
                    ),
                    'holidays_group_id',
                    $installer->getTable('blackbird_ets_holidays_group'),
                    'holidays_group_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_preparation_time_rule_holidays_group',
                        'preparation_time_rule_id',
                        'blackbird_ets_preparation_time_rule',
                        'preparation_time_rule_id'
                    ),
                    'preparation_time_rule_id',
                    $installer->getTable('blackbird_ets_preparation_time_rule'),
                    'preparation_time_rule_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Estimate Time Shipping Preparation Time Rule Holidays Group');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_public_holiday'
         */
        if (!$installer->tableExists('blackbird_ets_public_holiday')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_public_holiday'))
                ->addColumn(
                    'public_holiday_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'auto_increment' => true,
                        'primary' => true,
                    ],
                    'Public Holiday Id'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Name'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Description'
                )
                ->addColumn(
                    'rule_date',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Rule Date'
                )
                ->addColumn(
                    'date_type',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Date Type'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_public_holiday',
                        [
                            'name',
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'name',
                    ],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Estimate Time Shipping Public Holiday');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_public_holidays_group'
         */
        if (!$installer->tableExists('blackbird_ets_public_holidays_group')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_public_holidays_group'))
                ->addColumn(
                    'holidays_group_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Holidays Group Id'
                )
                ->addColumn(
                    'public_holiday_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Public Holiday Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_public_holidays_group',
                        [
                            'public_holiday_id',
                        ],
                        null
                    ),
                    [
                        'public_holiday_id',
                    ],
                    []
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_public_holidays_group',
                        'holidays_group_id',
                        'blackbird_ets_holidays_group',
                        'holidays_group_id'
                    ),
                    'holidays_group_id',
                    $installer->getTable('blackbird_ets_holidays_group'),
                    'holidays_group_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_public_holidays_group',
                        'public_holiday_id',
                        'blackbird_ets_public_holiday',
                        'public_holiday_id'
                    ),
                    'public_holiday_id',
                    $installer->getTable('blackbird_ets_public_holiday'),
                    'public_holiday_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Estimate Time Shipping Public Holidays Group');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_shipping_time_rule'
         */
        if (!$installer->tableExists('blackbird_ets_shipping_time_rule')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_shipping_time_rule'))
                ->addColumn(
                    'shipping_time_rule_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'auto_increment' => true,
                        'primary' => true,
                    ],
                    'Shipping Time Rule Id'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Name'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Description'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Is Active'
                )
                ->addColumn(
                    'shipping_time',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Shipping Time'
                )
                ->addColumn(
                    'shipping_days',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Shipping Days'
                )
                ->addColumn(
                    'cart_conditions_serialized',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Cart Conditions Serialized'
                )
                ->setComment('ETS Shipping Time Rule');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_shipping_time_rule_holidays_group'
         */
        if (!$installer->tableExists('blackbird_ets_shipping_time_rule_holidays_group')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_shipping_time_rule_holidays_group'))
                ->addColumn(
                    'holidays_group_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Holidays Group Id'
                )
                ->addColumn(
                    'shipping_time_rule_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Shipping Time Rule Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_shipping_time_rule_holidays_group',
                        [
                            'shipping_time_rule_id',
                        ],
                        null
                    ),
                    [
                        'shipping_time_rule_id',
                    ],
                    []
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_shipping_time_rule_holidays_group',
                        'holidays_group_id',
                        'blackbird_ets_holidays_group',
                        'holidays_group_id'
                    ),
                    'holidays_group_id',
                    $installer->getTable('blackbird_ets_holidays_group'),
                    'holidays_group_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_shipping_time_rule_holidays_group',
                        'shipping_time_rule_id',
                        'blackbird_ets_shipping_time_rule',
                        'shipping_time_rule_id'
                    ),
                    'shipping_time_rule_id',
                    $installer->getTable('blackbird_ets_shipping_time_rule'),
                    'shipping_time_rule_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('ETS Shipping Time Rule Holidays Group');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_preparation_time_rule_website'
         */
        if (!$installer->tableExists('blackbird_ets_preparation_time_rule_website')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_preparation_time_rule_website'))
                ->addColumn(
                    'preparation_time_rule_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Preparation Time Rule Id'
                )
                ->addColumn(
                    'website_id',
                    Table::TYPE_SMALLINT,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Website Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_preparation_time_rule_website',
                        [
                            'preparation_time_rule_id',
                        ],
                        null
                    ),
                    [
                        'preparation_time_rule_id',
                    ],
                    []
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_preparation_time_rule_website',
                        'preparation_time_rule_id',
                        'blackbird_ets_preparation_time_rule',
                        'preparation_time_rule_id'
                    ),
                    'preparation_time_rule_id',
                    $installer->getTable('blackbird_ets_preparation_time_rule'),
                    'preparation_time_rule_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_preparation_time_rule_website',
                        'website_id',
                        'store_website',
                        'website_id'
                    ),
                    'website_id',
                    $installer->getTable('store_website'),
                    'website_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('ETS Preparation Time Rule Website');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_shipping_time_rule_website'
         */
        if (!$installer->tableExists('blackbird_ets_shipping_time_rule_website')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_shipping_time_rule_website'))
                ->addColumn(
                    'shipping_time_rule_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Shipping Time Rule Id'
                )
                ->addColumn(
                    'website_id',
                    Table::TYPE_SMALLINT,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                    ],
                    'Website Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'blackbird_ets_shipping_time_rule_website',
                        [
                            'shipping_time_rule_id',
                        ],
                        null
                    ),
                    [
                        'shipping_time_rule_id',
                    ],
                    []
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_shipping_time_rule_website',
                        'shipping_time_rule_id',
                        'blackbird_ets_shipping_time_rule',
                        'shipping_time_rule_id'
                    ),
                    'shipping_time_rule_id',
                    $installer->getTable('blackbird_ets_shipping_time_rule'),
                    'shipping_time_rule_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_shipping_time_rule_website',
                        'website_id',
                        'store_website',
                        'website_id'
                    ),
                    'website_id',
                    $installer->getTable('store_website'),
                    'website_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('ETS Shipping Time Rule Website');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'blackbird_ets_estimated_date'
         */
        if (!$installer->tableExists('blackbird_ets_estimated_date')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('blackbird_ets_estimated_date'))
                ->addColumn(
                    'estimated_date_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                        'primary' => true,
                        'auto_increment' => true,
                    ],
                    'Estimated Date Id'
                )
                ->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Quote Id'
                )
                ->addColumn(
                    'quote_item_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Quote Item Id'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Order Id'
                )
                ->addColumn(
                    'order_item_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'precision' => '10',
                    ],
                    'Order Item Id'
                )
                ->addColumn(
                    'date',
                    Table::TYPE_DATE,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Date'
                )
                ->addColumn(
                    'is_delivery',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Is Delivery'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_estimated_date',
                        'quote_id',
                        'quote',
                        'entity_id'
                    ),
                    'quote_id',
                    $installer->getTable('quote'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_estimated_date',
                        'order_item_id',
                        'sales_order_item',
                        'item_id'
                    ),
                    'order_item_id',
                    $installer->getTable('sales_order_item'),
                    'item_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_estimated_date',
                        'quote_item_id',
                        'quote_item',
                        'item_id'
                    ),
                    'quote_item_id',
                    $installer->getTable('quote_item'),
                    'item_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'blackbird_ets_estimated_date',
                        'order_id',
                        'sales_order',
                        'entity_id'
                    ),
                    'order_id',
                    $installer->getTable('sales_order'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('ETS Dates');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
