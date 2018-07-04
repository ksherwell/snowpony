<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class OrderItemRepository implements \Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item
     */
    protected $resource;

    /**
     * @var Order\ItemFactory
     */
    protected $factory;

    /**
     * OrderItemRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item $resource
     * @param Order\ItemFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item $resource,
        \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param Data\WarehouseOrderItemInterface $item
     * @return Data\WarehouseOrderItemInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseOrderItemInterface $item)
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
     * @return CustomerGroup|Store
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Order Item with id "%1" does not exist.', $id));
        }
        return $model;
    }

    /**
     * @param int $orderItemId
     *
     * @return Order\Item
     */
    public function getByOrderItemId($orderItemId)
    {
        $model = $this->factory->create()->getCollection()
            ->addFieldToFilter(Data\WarehouseOrderItemInterface::ORDER_ITEM_ID, $orderItemId)
            ->getFirstItem();

        return $model;
    }

    /**
     * @param Data\WarehouseOrderItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseOrderItemInterface $item)
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
}
