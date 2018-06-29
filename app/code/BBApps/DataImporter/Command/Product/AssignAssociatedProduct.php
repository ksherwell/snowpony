<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\Product as ProductImport;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use BBApps\DataImporter\Command\AbstractImport;
use BBApps\DataImporter\Helper\Data;
use BBApps\DataImporter\Model\Csv;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignAssociatedProduct extends AbstractImport
{
    protected $commandName = 'assign-associated-products';
    protected $commandDescription = 'Assign Associated Products';
    protected $fileName = 'assign_associated_products.csv';
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var OptionsFactory
     */
    private $optionsFactory;

    public function __construct(
        Csv $csv,
        Data $helper,
        State $state,
        ProductRepositoryInterface $productRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        OptionsFactory $optionsFactory
    ) {
        parent::__construct($csv);

        $state->setAreaCode(Area::AREA_ADMINHTML);
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->optionsFactory = $optionsFactory;
    }

    protected function isFileRequired()
    {
        return false;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        $sku = trim($rowData['sku']);
        $configurableVariations = trim($rowData['configurable_variations']);
        if (!empty($sku) && !empty($configurableVariations)) {
            $product = $this->getProductBySku($sku);
            if ($product) {
                $product->setTypeId(Configurable::TYPE_CODE);
                $variations = explode(ProductImport::PSEUDO_MULTI_LINE_SEPARATOR, $configurableVariations);

                $associatedProductIds = [];
                $configurableAttributesData = [];
                $createOption = false;
                foreach ($variations as $variation) {
                    $values = $this->parseVariation($variation);
                    if (!empty($values['sku'])) {
                        $childProduct = $this->getProductBySku($values['sku']);
                        if ($childProduct) {
                            $associatedProductIds[] = $childProduct->getId();
                        }
                        unset($values['sku']);
                    }

                    if (!$createOption) {
                        foreach ($values as $_code => $_value) {
                            $attribute = $this->getAttributeByCode($_code);
                            if ($attribute) {
                                $configurableAttributesData[] = [
                                    'attribute_id' => $attribute->getId(),
                                    'code' => $_code,
                                    'label' => $attribute->getStoreLabel(),
                                    'position' => '0',
                                    'values' => [
                                        [
                                            'attribute_id' => $attribute->getId(),
                                            'value_index' => $_value,
                                        ]
                                    ],
                                ];

                                $createOption = true;
                            }
                        }
                    }
                }

                // create option sample
                $configurableOptions = $this->optionsFactory->create($configurableAttributesData);
                $extensionConfigurableAttributes = $product->getExtensionAttributes();
                $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
                $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
                $product->setExtensionAttributes($extensionConfigurableAttributes);
                try {
                    $this->productRepository->save($product);

                    $output->writeln('Sku ' . $sku . ' Updated Successfully');
                } catch (\Exception $e) {
                    $output->writeln('Error: ' . $e->getMessage() . ' Updated Un-successfully.');
                }
            } else {
                $output->writeln('Sku ' . $sku . ' Updated Un-successfully.');
            }
        }
    }

    private function getAttributeByCode($attributeCode)
    {
        $attribute = null;
        try {
            $attribute = $this->attributeRepository->get($attributeCode);
        } catch (\Exception $e) {
            // do nothing
        }

        return $attribute;
    }

    private function getProductBySku($sku)
    {
        $product = null;
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            // do nothing
        }

        return $product;
    }

    private function parseVariation($variation)
    {
        $values = [];
        $tmpArr = explode(',', $variation);
        foreach ($tmpArr as $tmp) {
            list($code, $value) = explode(ProductImport::PAIR_NAME_VALUE_SEPARATOR, $tmp, 2);

            $values[$code] = $value;
        }

        return $values;
    }
}
