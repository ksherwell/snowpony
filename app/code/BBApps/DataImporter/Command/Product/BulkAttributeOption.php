<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

class BulkAttributeOption extends AttributeOption
{
    protected $commandName = 'bulk-import-attribute-option';
    protected $commandDescription = 'Bulk Import Attribute Option';
    protected $fileName = 'import_product_attribute_options.csv';

    protected function configure()
    {
        if ($this->commandName) {
            $this->setName('bbapps:' . $this->commandName)->setDescription($this->commandDescription);
        }

        $this->addArgument(
            self::INPUT_KEY_FILE,
            InputArgument::REQUIRED,
            'Name of the file'
        );

        $this->addArgument(
            self::INPUT_KEY_ATTRIBUTE_CODE,
            InputArgument::REQUIRED,
            'Name of the attribute code'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        if (!$this->beforeExecute($input, $output)) {
            return Cli::RETURN_FAILURE;
        };

        $this->output = $output;
        try {
            $data = [];
            $sourceCsv = $this->csv->createSourceCsvModel(self::IMPORT_EXPORT_DIR . $this->fileName);
            foreach ($sourceCsv as $rowNum => $rowData) {
                $data[] = $this->processRow($rowData, $input, $output);
            }

            if ($this->attributeOption->bulkImportAttributeOption($data, $this->getAttributeCode())) {
                $output->writeln('Attribute Option Imported Successfully');
            } else {
                $output->writeln('Attribute Option Imported Un-successfully. Check the error in log file');
            }
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
        }

        if (!$this->afterExecute($input, $output)) {
            return Cli::RETURN_FAILURE;
        };

        return Cli::RETURN_SUCCESS;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        // convert store name into store id in row data
        return $this->formatRowData($rowData);
    }

    protected function beforeExecute(InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        $file = $input->getArgument(self::INPUT_KEY_FILE);
        if ($file) {
            $this->fileName = $file;
        }
        if (!$this->fileName) {
            $output->writeln('Please specify file name');
            return false;
        }

        return parent::beforeExecute($input, $output);
    }
}
