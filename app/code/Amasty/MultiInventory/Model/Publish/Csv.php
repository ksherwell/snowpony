<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Publish;

use Magento\Framework\App\Filesystem\DirectoryList;

class Csv
{
    const ENCLOSE = '"';

    const DELIMETER = ',';

    const PATH = '/var/amasty_export_csv/';

    /**
     * Source file handler.
     *
     * @var \Magento\Framework\Filesystem\File\Write
     */
    private $fileHandler;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\File\WriteFactory
     */
    public $file;

    /**
     * @var file
     */
    public $filename;

    /**
     * Publisher constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\WriteFactory $directory
     * @param \Magento\Framework\Filesystem\File\WriteFactory $file
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directory,
        \Magento\Framework\Filesystem\File\WriteFactory $file
    ) {
        $this->filesystem = $filesystem;
        $this->file       = $file;
        $this->directory  = $directory;
    }

    /**
     * @return string
     */
    public function getPathFile()
    {
        $dir  = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $path = $dir->getAbsolutePath() . self::PATH;
        if (!$dir->isExist($path)) {
            $directory = $this->directory->create($path);
            $directory->create();
        }

        return $path;
    }

    /**
     * @return file
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function init($filename, $type = 'w')
    {
        $path = $this->getPathFile();
        $file    = $this->file;
        $allPath = $path  . $filename;
        $this->setFilename($allPath);
        $this->fileHandler = $file->create(
            $allPath,
            \Magento\Framework\Filesystem\DriverPool::FILE,
            $type
        );
    }

    /**
     * @param array $rowData
     * @return $this
     */
    public function writeRow(array $rowData)
    {
        foreach ($rowData as $key => &$value) {
            $value = str_replace(
                ["\r\n", "\n", "\r"],
                ' ',
                $value
            );
        }
        $this->fileHandler->writeCsv(
            $rowData,
            self::DELIMETER,
            self::ENCLOSE
        );

        return $this;
    }

    /**
     * Desctuct
     */
    public function destruct()
    {
        if (is_object($this->fileHandler)) {
            $this->fileHandler->close();
        }
    }
}
