<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Stock;

use Amasty\MultiInventory\Model\Warehouse\ItemRepository;
use Magento\Backend\App\Action\Context;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface as WarehouseRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;

class InlineEdit extends \Amasty\MultiInventory\Controller\Adminhtml\Stock
{
    /** @var JsonFactory */
    private $jsonFactory;

    /**
     * @var WarehouseRepository
     */
    private $warehouseRepository;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $indexer;

    /**
     * InlineEdit constructor.
     *
     * @param Context                                                  $context
     * @param WarehouseRepository                                      $warehouseRepository
     * @param WarehouseFactory                                         $warehouseFactory
     * @param ItemFactory                                              $itemFactory
     * @param JsonFactory                                              $jsonFactory
     * @param ItemRepository                                           $itemRepository
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $indexer
     */
    public function __construct(
        Context $context,
        WarehouseRepository $warehouseRepository,
        WarehouseFactory $warehouseFactory,
        ItemFactory $itemFactory,
        JsonFactory $jsonFactory,
        ItemRepository $itemRepository,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $indexer
    ) {
        parent::__construct($context);
        $this->warehouseRepository = $warehouseRepository;
        $this->jsonFactory = $jsonFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->itemFactory = $itemFactory;
        $this->itemRepository = $itemRepository;
        $this->indexer = $indexer;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        $changedProducts = [];
        foreach ($postItems as $item) {
            $productId = $item['entity_id'];
            $warehouses = $this->scopeWh($item);
            try {
                if (!empty($warehouses)) {
                    foreach ($warehouses as $warehouse) {
                        $this->change($productId, $warehouse);
                        $changedProducts[] = $productId;
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $e->getMessage();
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $e->getMessage();
                $error = true;
            } catch (\Exception $e) {
                $messages[] = __('Something went wrong while saving the stock.');
                $error = true;
            }
        }
        if (!$error && !empty($changedProducts)) {
            // recalculate total stock
            $this->indexer->reindexList($changedProducts);
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $product
     * @param $warehouse
     */
    private function change($product, $warehouse)
    {
        $item = $this->itemRepository->getByProductWarehouse($product, $warehouse['id']);

        if (!$item->getId() && $warehouse['qty'] == 0 && $warehouse['room'] == '') {
            return; // Prevent creation of zero records
        }

        $model = $this->warehouseRepository->getById($warehouse['id']);
        if (!$model->getIsGeneral()) {
            $object = $this->itemFactory->create();
            $object->setProductId($product);
            $object->setQty($warehouse['qty']);
            $object->setRoomShelf($warehouse['room']);
            $object->setStockStatus($warehouse['stock_status']);
            $model->addItem($object);
        }
    }

    /**
     * @param $items
     * @return array
     */
    public function scopeWh($items)
    {
        $fields = ['qty', 'room', 'stock_status'];
        $stocksWh = [];
        foreach ($items as $key => $value) {
            if ($key != 'entity_id') {
                $id = $this->warehouseRepository->getByCode($key)->getId();
                $stocksWh[$key]['id'] = $id;
                foreach ($value as $field => $text) {
                    if (in_array($field, $fields)) {
                        $stocksWh[$key][$field] = $text;
                    }
                }
            }
        }

        return $stocksWh;
    }
}
