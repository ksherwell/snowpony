<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AttributeOption extends \BBApps\DataImporter\Command\AbstractImport
{
    const INPUT_KEY_ATTRIBUTE_CODE = 'attribute_code';

    protected $commandName = 'import-attribute-option';
    protected $commandDescription = 'Import Attribute Option';
    protected $fileName = 'import_product_attribute_options.csv';

    protected $attributeCode = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \BBApps\DataImporter\Model\AttributeOption
     */
    protected $attributeOption;

    protected $storeIds;
    /**
     * @param \BBApps\DataImporter\Model\Csv $csv
     * @param \BBApps\DataImporter\Model\AttributeOption $attributeOption
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \BBApps\DataImporter\Model\Csv $csv,
        \BBApps\DataImporter\Model\AttributeOption $attributeOption,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($csv);

        $this->storeManager = $storeManager;
        $this->attributeOption = $attributeOption;
    }

    protected function configure()
    {
        parent::configure();

        $this->addArgument(
            self::INPUT_KEY_ATTRIBUTE_CODE,
            InputArgument::REQUIRED,
            'Name of the attribute code'
        );
    }

    protected function isFileRequired()
    {
        return true;
    }

    protected function beforeExecute(InputInterface $input, OutputInterface $output)
    {
        if (!parent::beforeExecute($input, $output)) {
            return false;
        }

        $attributeCode = $input->getArgument(self::INPUT_KEY_ATTRIBUTE_CODE);
        if (!$attributeCode) {
            $output->writeln('Please specify attribute code');
            return false;
        }
        $this->setAttributeCode($attributeCode);
        $output->writeln('Start Import');

        return true;
    }

    protected function afterExecute(InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        $output->writeln('Finish Import');

        return true;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        // convert store name into store id in row data
        $rowDataFormat = $this->formatRowData($rowData);

        if ($this->attributeOption->importAttributeOption($rowDataFormat, $this->getAttributeCode())) {
            $output->writeln('Attribute Option Imported Successfully');
        } else {
            $output->writeln('Attribute Option Imported Un-successfully. Check the error in log file');
        }
    }

    protected function formatRowData($rowData)
    {
        $rowDataFormat = [];
        foreach ($rowData as $key => $value) {
            if (is_numeric($key)) {
                $rowDataFormat[$key] = $value;
            } elseif ($this->getStoreIdByName($key) !== null) {
                $rowDataFormat[$this->getStoreIdByName($key)] = $value;
            } elseif (in_array($key, ['Swatch', 'swatch'])) {
                $rowDataFormat['swatch'] = $value;
            }
        }

        return $rowDataFormat;
    }

    protected function getStoreIdByName($name)
    {
        if (in_array($name, $this->getStoreIds())) {
            return array_search($name, $this->getStoreIds());
        }

        return null;
    }

    protected function getStoreIds()
    {
        if (!$this->storeIds) {
            $storeIds = [];
            $stores = $this->storeManager->getStores(true);
            foreach ($stores as $store) {
                $storeIds[$store->getId()] = $store->getName();
            }
            $this->storeIds = $storeIds;
        }

        return $this->storeIds;
    }

    protected function setAttributeCode($attributeCode)
    {
        $this->attributeCode = $attributeCode;
    }

    protected function getAttributeCode()
    {
        return $this->attributeCode;
    }
}
