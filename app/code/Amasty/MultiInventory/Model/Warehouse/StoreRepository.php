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

class StoreRepository implements \Amasty\MultiInventory\Api\WarehouseStoreRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup|\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store
     */
    protected $resource;

    /**
     * @var CustomerGroupFactory|StoreFactory
     */
    protected $factory;

    /**
     * WarehouseStoreRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store $resource
     * @param StoreFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store $resource,
        \Amasty\MultiInventory\Model\Warehouse\StoreFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param Data\WarehouseStorenterface $item
     * @return Data\WarehouseStorenterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseStoreInterface $item)
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
            throw new NoSuchEntityException(__('Warehouse Store with id "%1" does not exist.', $id));
        }
        return $model;
    }

    /**
     * @param Data\WarehouseStorenterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseStoreInterface $item)
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
