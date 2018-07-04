<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Delete extends \Amasty\MultiInventory\Controller\Adminhtml\Export
{
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
     * Delete constructor.
     * @param Action\Context $context
     * @param \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\IO\File $file
     */
    public function __construct(
        Action\Context $context,
        \Amasty\MultiInventory\Api\ExportRepositoryInterface $repository,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->repository = $repository;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('export_id');
        if ($id) {
            try {
                $export = $this->repository->getById($id);
                $filename = $export->getFile();
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the export file.'));
                $path    = $this->fileSystem
                    ->getDirectoryWrite(DirectoryList::MEDIA)
                    ->getAbsolutePath('/') . \Amasty\MultiInventory\Model\Export::PATH_EXPORT . $filename;
                if ($this->file->fileExists($path)) {
                    $this->file->rm($path);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/index');
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a export to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
