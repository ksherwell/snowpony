<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_DELETE = 'amasty_multi_inventory/export/delete';

    const URL_PATH_DOWNLOAD = 'amasty_multi_inventory/export/download';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\MultiInventory\Api\ExportRepositoryInterface
     */
    private $repository;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\IO\File
     */
    private $file;

    /**
     * ExportActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File $file,
        \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['export_id'])) {
                    $export = $this->repository->getById($item['export_id']);
                    $path = $path = $this->fileSystem
                            ->getDirectoryWrite(DirectoryList::MEDIA)
                            ->getAbsolutePath('/') . \Amasty\MultiInventory\Model\Export::PATH_EXPORT . $export->getFile();
                    if ($this->file->fileExists($path)) {
                        $url = $this->storeManager
                            ->getStore()
                            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                        $item[$this->getData('name')]['download'] = [
                            'href' => $this->urlBuilder->getUrl(
                                $url
                                . \Amasty\MultiInventory\Model\Export::PATH_EXPORT
                                . $export->getFile()
                            ),
                            'label' => __('Download'),
                        ];
                    }

                    $item[$this->getData('name')]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_DELETE,
                            [
                                'export_id' => $item['export_id']
                            ]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.title }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.file }" record?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
