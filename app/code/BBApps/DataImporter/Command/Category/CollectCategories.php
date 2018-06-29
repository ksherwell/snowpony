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

namespace BBApps\DataImporter\Command\Category;

use Magento\Framework\Filesystem\DirectoryList;
use BBApps\DataImporter\Model\Csv;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectCategories extends \Symfony\Component\Console\Command\Command
{
    const IMPORT_EXPORT_DIR = 'importexport/';
    const INPUT_KEY_FILE = 'file';
    const CATEGORIES_DELIMITER = ',';
    const CATEGORIES_PATH_DELIMITER = '/';

    private $commandName = 'collect-categories';
    private $commandDescription = 'Collect Categories';
    private $fileName = 'product_categories.csv';
    private $formatFileName = 'product_categories_format.csv';
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
            $outputCategories = [];
            foreach ($inputCsv as $rowNum => $rowData) {
                if (!empty($rowData['categories'])) {
                    $_categoriesData = $rowData['categories'];
                    $_categoriesPathArr = explode(self::CATEGORIES_DELIMITER, $_categoriesData);
                    foreach ($_categoriesPathArr as $_categoryPath) {
                        if (!in_array($_categoryPath, $outputCategories)) {
                            $outputCategories[] = $_categoryPath;
                            $_categoriesArr = explode(self::CATEGORIES_PATH_DELIMITER, $_categoryPath);
                            $name = array_pop($_categoriesArr);

                            $outputCsv->writeRow(
                                [
                                    'name' => $name,
                                    'parent' => implode(self::CATEGORIES_PATH_DELIMITER, $_categoriesArr)
                                ]
                            );
                        }
                    }
                }
            }

            $output->writeln('Collect Successfully');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
