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

class Clear extends \Amasty\MultiInventory\Controller\Adminhtml\Import
{

    const PATH = 'amasty_multiinventory/import';

    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ImportFactory
     */
    private $importFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface
     */
    private $importRepository;

    /**
     * Send constructor.
     * @param Action\Context $context
     * @param \Amasty\MultiInventory\Helper\Data $helper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Amasty\MultiInventory\Model\Warehouse\ImportFactory $importFactory
     * @param \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface $importRepository
     */
    public function __construct(
        Action\Context $context,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Amasty\MultiInventory\Model\Warehouse\ImportFactory $importFactory,
        \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface $importRepository
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->importFactory = $importFactory;
        $this->importRepository = $importRepository;
    }

    /**
     * Run
     */
    public function execute()
    {
        $number = $this->getRequest()->getParam('number');
        try {
            $collection = $this->importFactory->create()->getCollection()
                ->addFieldToFilter('import_number', $number);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $this->importRepository->delete($item);
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
