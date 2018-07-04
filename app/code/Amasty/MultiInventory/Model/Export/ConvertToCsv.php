<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends Convert
{
    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getFile($filename)
    {
        $file = \Amasty\MultiInventory\Model\Export::PATH_EXPORT . $filename;
        $this->directory->create('amasty_export_stock');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $headers = $this->factory->create()->getHeaders();
        $stream->writeCsv($headers);
        $items = $this->getItems();
        foreach ($this->partCollection($items) as $item) {
            $stream->writeCsv($this->getRowData($item));
        }
        $stream->unlock();
        $stream->close();
        $this->saveFile($filename);
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => false
        ];
    }

    /**
     * @param $filename
     * @param $collection
     * @return array
     */
    public function getFileFromCollection($filename, $collection)
    {
        $file = \Amasty\MultiInventory\Model\Export::PATH_EXPORT . $filename;
        $this->directory->create('amasty_export_stock');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $headers = $this->factory->create()->getHeaders();
        $stream->writeCsv($headers);
        foreach ($this->partCollection($collection) as $item) {
            $stream->writeCsv($this->getRowData($item));
        }
        $stream->unlock();
        $stream->close();
        $this->saveFile($filename);
        return [
            'type' => 'filename',
            'value' => $this->directory->getAbsolutePath() . $file,
            'rm' => false
        ];
    }
}
