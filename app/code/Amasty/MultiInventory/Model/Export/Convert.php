<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Amasty\MultiInventory\Ui\Component\MassAction\FileFilter as Filter;

/**
 * Class ConvertToCsv
 */
abstract class Convert
{
    use \Amasty\MultiInventory\Traits\Additional;

    /**
     * @var WriteInterface
     */
    public $directory;

    /**
     * @var \Amasty\MultiInventory\Model\ExportFactory
     */
    public $factory;

    /**
     * @var \Amasty\MultiInventory\Api\ExportRepositoryInterface
     */
    private $repository;

    /**
     * ConvertToCsv constructor.
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param \Amasty\MultiInventory\Model\ExportFactory $factory
     * @param \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        \Amasty\MultiInventory\Model\ExportFactory $factory,
        \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->factory = $factory;
        $this->repository = $repository;
    }

    abstract public function getFile($filename);

    /**
     * @return mixed
     */
    public function getItems()
    {
        $component = $this->filter->getComponent();
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();

        return $dataProvider->getSqlItems();
    }

    /**
     * @param $filename
     */
    public function saveFile($filename)
    {
        $export = $this->factory->create();
        $export->setFile($filename);
        $this->repository->save($export);
    }

    /**
     * @param $data
     * @return array
     */
    public function getRowData($data)
    {
        $headers = $this->factory->create()->getHeaders();
        $arraySend = [];
        foreach ($headers as $field) {
            $arraySend[] = $data[$field];
        }

        return $arraySend;
    }
}
