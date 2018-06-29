<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Category;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Category extends \BBApps\DataImporter\Command\AbstractImport
{
    protected $commandName = 'import-category';
    protected $commandDescription = 'Import Category';
    protected $fileName = 'categories.csv';

    /**
     * @var \BBApps\DataImporter\Model\CategoryImport
     */
    private $categoryImport;

    public function __construct(
        \BBApps\DataImporter\Model\Csv $csv,
        \BBApps\DataImporter\Model\CategoryImport $categoryImport
    ) {
        parent::__construct($csv);

        $this->categoryImport = $categoryImport;
    }

    protected function isFileRequired()
    {
        return false;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        if ($this->categoryImport->import($rowData)) {
            $output->writeln('Category Imported Successfully');
        } else {
            $output->writeln('Category Imported Un-successfully. Check the error in log file');
        }
    }
}
