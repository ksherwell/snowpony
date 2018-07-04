<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Model\Warehouse;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class Send extends \Amasty\MultiInventory\Controller\Adminhtml\Import
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
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $stockRepository;

    /**
     * Send constructor.
     * @param Action\Context $context
     * @param \Amasty\MultiInventory\Helper\Data $helper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param Warehouse\ImportFactory $importFactory
     * @param \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface $importRepository
     * @param Warehouse\ItemFactory $itemFactory
     */
    public function __construct(
        Action\Context $context,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Amasty\MultiInventory\Model\Warehouse\ImportFactory $importFactory,
        \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface $importRepository,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->importFactory = $importFactory;
        $this->importRepository = $importRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Send data from file to DB
     */
    public function execute()
    {
        $imports = $this->getRequest()->getParam('import');

        try {
            foreach ($imports as $import) {
                $newImport = $this->importFactory->create();

                $oldQty = 0;
                $stockItem = $this->stockRepository
                    ->getByProductWarehouse($import['product_id'], $import['warehouse_id']);

                if ($stockItem->getId()) {
                    $oldQty = $stockItem->getQty();
                }
                $import['new_qty'] = $this->setOperations($import['qty'], $oldQty);
                $import['qty'] = $oldQty;
                $newImport->setData($import);

                $this->importRepository->save($newImport);
            }
            $result = ['response' => true];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        }
    }

    /**
     * @param $value
     * @param $oldQty
     * @return int
     */
    private function setOperations($value, $oldQty)
    {
        $newQty = 0;
        if (strpos($value, "-") !== false) {
            $newQty = $oldQty - (int)substr($value, 1);
        } elseif (strpos($value, "+") !== false) {
            $newQty = $oldQty + (int)substr($value, 1);
        } else {
            $newQty = $value;
        }

        return $newQty;
    }
}
