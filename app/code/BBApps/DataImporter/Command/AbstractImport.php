<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImport extends \Symfony\Component\Console\Command\Command
{
    const IMPORT_EXPORT_DIR = 'importexport/';
    const INPUT_KEY_FILE = 'file';

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

        $this->addArgument(
            self::INPUT_KEY_FILE,
            $this->isFileRequired() ? InputArgument::REQUIRED : InputArgument::OPTIONAL,
            'Name of the file'
        );
    }

    /**
     * Returns if file argument is required
     *
     * @return bool
     */
    abstract protected function isFileRequired();

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->beforeExecute($input, $output)) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        };

        $this->output = $output;
        try {
            $sourceCsv = $this->csv->createSourceCsvModel(self::IMPORT_EXPORT_DIR . $this->fileName);
            foreach ($sourceCsv as $rowNum => $rowData) {
                $this->processRow($rowData, $input, $output);
            }
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
        }

        if (!$this->afterExecute($input, $output)) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        };

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    protected function beforeExecute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument(self::INPUT_KEY_FILE);
        if ($file) {
            $this->fileName = $file;
        }
        if (!$this->fileName) {
            $output->writeln('Please specify file name');
            return false;
        }

        return true;
    }

    protected function afterExecute(InputInterface $input, OutputInterface $output)
    {
        return true;
    }

    abstract protected function processRow($rowData, InputInterface $input, OutputInterface $output);
}
