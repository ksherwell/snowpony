<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class Deletefile extends \Amasty\MultiInventory\Controller\Adminhtml\Import
{

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
     * Deletefile constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Filesystem\Io\File $filesystem
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem\Io\File $filesystem,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->fileSystem = $filesystem;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Delete file from server
     */
    public function execute()
    {
        $path = $this->getRequest()->getParam('path');
        try {
            if ($path) {
                if ($this->fileSystem->fileExists($path)) {
                    $this->fileSystem->rm($path);
                }
            }
            $result = ['response' => true];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        }
    }
}
