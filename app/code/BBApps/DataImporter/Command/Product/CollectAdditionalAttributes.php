<?php
/**
 * BBApps DataImporter
 * The script is used to collect the attribute values from "additional_attributes" column in product import file
 * Example:
 * -- From: size=125ml|color=green
 * -- To: 2 columns with the values of size and color
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Magento\Framework\Filesystem\DirectoryList;
use BBApps\DataImporter\Model\Csv;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectAdditionalAttributes extends \Symfony\Component\Console\Command\Command
{
    const IMPORT_EXPORT_DIR = 'importexport/';
    const IMAGE_DIR = '/import/products/'; // in media/ folder
    const INPUT_KEY_FILE = 'file';
    const ATTRIBUTE_DELIMITER = '|';
    const ATTRIBUTE_VALUE_DELIMITER = '=';

    private $commandName = 'collect-additional-attributes';
    private $commandDescription = 'Collect Additional Attributes';
    private $fileName = 'additional_attributes.csv';
    private $formatFileName = 'additional_attributes_format.csv';
    /**
     * @var Csv
     */
    private $csv;
    /**
     * @var DirectoryList
     */
    private $directoryList;

    public function __construct(
        Csv $csv,
        DirectoryList $directoryList
    ) {
        parent::__construct();

        $this->csv = $csv;
        $this->directoryList = $directoryList;
    }

    protected function configure()
    {
        if ($this->commandName) {
            $this->setName('bbapps:' . $this->commandName)->setDescription($this->commandDescription);
        }

        $this->addArgument(
            self::INPUT_KEY_FILE,
            InputArgument::OPTIONAL,
            'Name of the file'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $inputCsv = $this->csv->createSourceCsvModel(self::IMPORT_EXPORT_DIR . $this->fileName);
            $outputCsv = $this->csv->createOutputCsvModel(self::IMPORT_EXPORT_DIR . $this->formatFileName);
            $attributesArr = [];
            foreach ($inputCsv as $rowNum => $rowData) {
                if (!empty($rowData['additional_attributes'])) {
                    $_additionalAttributes = $rowData['additional_attributes'];
                    $_additionalAttributesArr = explode(self::ATTRIBUTE_DELIMITER, $_additionalAttributes);
                    foreach ($_additionalAttributesArr as $_additionalAttribute) {
                        list($attributeCode, $value) = explode(self::ATTRIBUTE_VALUE_DELIMITER, $_additionalAttribute);
                        if (!isset($attributesArr[$attributeCode])) {
                            $attributesArr[$attributeCode] = [$value];
                        } elseif ($value && !in_array($value, $attributesArr[$attributeCode])) {
                            $attributesArr[$attributeCode][] = $value;
                        }
                    }
                }
            }

            $outputData = [];
            foreach ($attributesArr as $attributeCode => $values) {
                foreach ($values as $key => $value) {
                    $outputData[$key][$attributeCode] = trim($value);
                }
            }

            foreach ($outputData as $_rowData) {
                $outputCsv->writeRow($_rowData);
            }
            $output->writeln('Collect Successfully');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
