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

class ShippingRepository implements \Amasty\MultiInventory\Api\WarehouseShippingRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping
     */
    protected $resource;

    /**
     * @var ShippingFactory
     */
    protected $factory;

    /**
     * ShippingRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping $resource
     * @param ShippingFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping $resource,
        \Amasty\MultiInventory\Model\Warehouse\ShippingFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param Data\WarehouseShippingInterface $item
     * @return Data\WarehouseShippingInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseShippingInterface $item)
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
     * @return Data\WarehouseShippingInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Shipping Method with id "%1" does not exist.', $id));
        }
        return $model;
    }

    /**
     * @param Data\WarehouseShippingInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseShippingInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the warehouse shipping %1',
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
