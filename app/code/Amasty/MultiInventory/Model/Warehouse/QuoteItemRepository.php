<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data;
use Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class QuoteItemRepository implements \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item
     */
    private $resource;

    /**
     * @var Quote\ItemFactory
     */
    private $factory;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory
     */
    private $collectionFactory;

    private $itemWarehouseStorage = [];

    /**
     * QuoteItemRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item $resource
     * @param Quote\ItemFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item $resource,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $collectionFactory,
        \Amasty\MultiInventory\Model\Warehouse\Quote\ItemFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param WarehouseQuoteItemInterface $item
     * @return WarehouseQuoteItemInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseQuoteItemInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * @param $id
     * @return \Amasty\MultiInventory\Model\Warehouse\Quote\Item
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Quote Item with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * @param WarehouseQuoteItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WarehouseQuoteItemInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the warehouse store: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $quoteItem
     *
     * @return int|null
     */
    public function getWarehouseIdByItem($quoteItem)
    {
        $quoteItemId = $quoteItem->getItemId();
        if (!isset($this->itemWarehouseStorage[$quoteItemId])) {
            $this->itemWarehouseStorage[$quoteItemId] = null;
            $warehouseQuote = $this->collectionFactory->create()
                ->addFieldToFilter(WarehouseQuoteItemInterface::QUOTE_ID, $quoteItem->getQuoteId())
                ->getData();

            if ($warehouseQuote) {
                foreach ($warehouseQuote as $warehouseData) {
                    $itemId = $warehouseData[WarehouseQuoteItemInterface::QUOTE_ITEM_ID];

                    $this->itemWarehouseStorage[$itemId]
                        = $warehouseData[WarehouseQuoteItemInterface::WAREHOUSE_ID];
                }

            }
        }

        return $this->itemWarehouseStorage[$quoteItemId];
    }
}
