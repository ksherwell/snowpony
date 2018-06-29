<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractExport extends \Symfony\Component\Console\Command\Command
{
    const IMPORT_EXPORT_DIR = 'importexport/';

    /** @var \BBApps\DataImporter\Model\Csv */
    protected $csv;
    protected $output;

    protected $commandName = null;
    protected $commandDescription = null;
    protected $fileName = null;

    public function __construct(
        \BBApps\DataImporter\Model\Csv $csv
    ) {
        $this->csv = $csv;

        parent::__construct();
    }

    protected function configure()
    {
        if ($this->commandName) {
            $this->setName('bbapps:' . $this->commandName)->setDescription($this->commandDescription);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->beforeExecute($input, $output)) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        };

        $destination = self::IMPORT_EXPORT_DIR . $this->fileName;
        $outputCsv = $this->csv->createOutputCsvModel($destination);
        $exportData = $this->getExportData();

        foreach ($exportData as $rowNum => $rowData) {
            $outputCsv->writeRow($rowData);
        }

        $output->writeln('Export file '. $destination . ' Successfully');
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    protected function beforeExecute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->fileName) {
            $output->writeln('Please specify file name');
            return false;
        }

        return true;
    }

    abstract protected function getExportData();
}
