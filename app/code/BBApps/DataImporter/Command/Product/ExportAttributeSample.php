<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportAttributeSample extends \BBApps\DataImporter\Command\AbstractExport
{
    protected $commandName = 'export-attribute-sample';
    protected $commandDescription = 'Export Attribute Sample';
    protected $fileName = 'product_attributes.csv';

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var \BBApps\DataImporter\Model\Attribute
     */
    protected $baseProductImport;

    public function __construct(
        \BBApps\DataImporter\Model\Csv $csv,
        \BBApps\DataImporter\Model\Attribute $baseProductImport
    ) {
        parent::__construct($csv);

        $this->baseProductImport = $baseProductImport;
    }

    protected function getExportData()
    {
        return $this->getAttributeSampleData();
    }

    public function getAttributeSampleData()
    {
        return [
            [
                'attribute_set' => 'Default',
                'attribute_group_name' => 'Product Details',
                'attribute_code' => 'attribute_sample',
                'default_label' => 'Attribute Sample',
                'frontend_labels' => '1:Attribute Sample English|2: Attribute Sample French|3: Attribute Sample German',
                'frontend_input' => 'select',
                'is_user_defined' => 1,
                'is_required' => 0,
                'is_global' => 1,
                'is_unique' => 0,
                'frontend_class' => '',
                'is_used_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_searchable' => 1,
                'search_weight' => 1,
                'is_visible_in_advanced_search' => 0,
                'is_comparable' => 1,
                'is_filterable' => 1,
                'is_filterable_in_search' => 1,
                'position' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'attribute_options' => '0:Option Small 1|0:Option Medium 1|0:Option Large 1|1:Option Small 2|'
                    .'1:Option Medium 2|1:Option Large 2'
            ]
        ];
    }
}
