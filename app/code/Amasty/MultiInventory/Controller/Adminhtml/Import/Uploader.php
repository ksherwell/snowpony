<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


/**
 *
 *
 *
 */
namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class Uploader extends \Amasty\MultiInventory\Controller\Adminhtml\Import
{

    const PATH = 'amasty_multiinventory/import';

    const MIME_OCTET_STREAM = 'application/octet-stream';

    const MIME_CSV = 'text/csv';

    const MIME_CSV_MS_EXCEL = 'application/vnd.ms-excel';

    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Uploader constructor.
     * @param Action\Context $context
     * @param \Amasty\MultiInventory\Helper\Data $helper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->fileSystem = $filesystem;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Upload file via ajax
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'import_file']);
            $uploader->setAllowedExtensions(['csv', 'xml']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory->getAbsolutePath(self::PATH));
            unset($result['tmp_name']);
            if ($result['type'] == self::MIME_OCTET_STREAM || $result["type"] == self::MIME_CSV_MS_EXCEL) {
                $result['type'] = self::MIME_CSV;
            }
            $result['url'] = $this->getTmpMediaUrl($result['file']);
            $result['message'] = __('The import file has been uploaded successfully. '
                . 'Please click "Import" to continue import.');
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        }
    }

    /**
     * @return string
     */
    private function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . self::PATH;
    }

    /**
     * @param $file
     * @return string
     */
    private function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * @param $file
     * @return string
     */
    private function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}