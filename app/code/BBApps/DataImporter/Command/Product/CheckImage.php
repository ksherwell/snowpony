<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Magento\Framework\Filesystem\DirectoryList;
use BBApps\DataImporter\Model\Csv;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckImage extends \Symfony\Component\Console\Command\Command
{
    const IMPORT_EXPORT_DIR = 'importexport/';
    const IMAGE_DIR = '/import/products/'; // in media/ folder
    const INPUT_KEY_FILE = 'file';

    private $commandName = 'check-image';
    private $commandDescription = 'Check Image';
    private $fileName = 'bbapps-formatted-images.csv';
    private $formatFileName = 'check_images_format.csv';
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
            $imageDir = $this->directoryList->getPath('media') . self::IMAGE_DIR;
            foreach ($inputCsv as $rowNum => $rowData) {
                if (!empty($rowData['image']) && !file_exists($imageDir . $rowData['image'])) {
                    $rowData['image'] = '';
                }

                $outputCsv->writeRow($rowData);
            }

            $output->writeln('Convert Successfully');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
