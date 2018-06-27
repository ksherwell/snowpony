<?php

namespace Aidalab\ImportNewStates\Setup;

use Magento\Directory\Helper\Data;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Directory data
     *
     * @var Data
     */
    private $directoryData;

    /**
     * Init
     *
     * @param Data $directoryData
     */
    public function __construct(Data $directoryData)
    {
        $this->directoryData = $directoryData;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Add All Australian states
         *
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         */
        $data = [
            ['AU', 'NSW', 'New South Wales'],
            ['AU', 'QLD', 'Queensland'],
            ['AU', 'SA', 'South Australia'],
            ['AU', 'TAS', 'Tasmania'],
            ['AU', 'VIC', 'Victoria'],
            ['AU', 'WA', 'Western Australia'],
        ];

        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $setup->getConnection()->insert($setup->getTable('directory_country_region'), $bind);
            $regionId = $setup->getConnection()->lastInsertId($setup->getTable('directory_country_region'));

            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $setup->getConnection()->insert($setup->getTable('directory_country_region_name'), $bind);
        }
    }
}
