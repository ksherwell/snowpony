<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Csv create new CSV file and add Error data in additional column
 */
class Csv
{
    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    private $sourceCsvFactory;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory
     */
    private $outputCsvFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    public function __construct(
        \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory,
        \Magento\ImportExport\Model\Export\Adapter\CsvFactory $outputCsvFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->sourceCsvFactory = $sourceCsvFactory;
        $this->outputCsvFactory = $outputCsvFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $sourceFile
     * @return \Magento\ImportExport\Model\Import\Source\Csv
     */
    public function createSourceCsvModel($sourceFile)
    {
        $obj = $this->sourceCsvFactory->create(
            [
                'file' => $sourceFile,
                'directory' => $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ]
        );
        return $obj;
    }

    /**
     * @param string $destination
     * @return \Magento\ImportExport\Model\Export\Adapter\Csv
     */
    public function createOutputCsvModel($destination)
    {
        return $this->outputCsvFactory->create(
            [
                'destination' => $destination
            ]
        );
    }
}
