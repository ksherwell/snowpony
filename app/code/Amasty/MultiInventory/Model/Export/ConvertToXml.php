<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Amasty\MultiInventory\Ui\Component\MassAction\FileFilter;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

class ConvertToXml extends Convert
{

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * ConvertToXml constructor.
     * @param Filesystem $filesystem
     * @param FileFilter $filter
     * @param \Amasty\MultiInventory\Model\ExportFactory $factory
     * @param \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository
     * @param ExcelFactory $excelFactory
     * @param \Magento\Ui\Model\Export\SearchResultIteratorFactory $iteratorFactory
     */
    public function __construct(
        Filesystem $filesystem,
        FileFilter $filter,
        \Amasty\MultiInventory\Model\ExportFactory $factory,
        \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository,
        ExcelFactory $excelFactory,
        \Magento\Ui\Model\Export\SearchResultIteratorFactory $iteratorFactory
    ) {
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        parent::__construct($filesystem, $filter, $factory, $repository);
    }


    /**
     * @param $filename
     * @return array
     */
    public function getFile($filename)
    {
        $file = \Amasty\MultiInventory\Model\Export::PATH_EXPORT . $filename;
        $this->directory->create('amasty_export_stock');
        $headers = $this->factory->create()->getHeaders();
        $searchResultItems = $this->getItems();

        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback' => [$this, 'getRowData'],
        ]);

        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($headers);
        $excel->write($stream, $filename);

        $stream->unlock();
        $stream->close();
        $this->saveFile($filename);

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => false
        ];
    }
}
