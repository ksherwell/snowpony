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

class CustomerGroupRepository implements \Amasty\MultiInventory\Api\WarehouseCustomerGroupRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup
     */
    protected $resource;

    /**
     * @var CustomerGroupFactory
     */
    protected $factory;

    /**
     * WarehouseCustomerGroupRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup $resource
     * @param CustomerGroupFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup $resource,
        \Amasty\MultiInventory\Model\Warehouse\CustomerGroupFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param Data\WarehouseCustomerGroupInterface $item
     * @return Data\WarehouseCustomerGroupInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseCustomerGroupInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * @param int $id
     * @return CustomerGroup
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Customer Group with id "%1" does not exist.', $id));
        }
        return $model;
    }

    /**
     * @param Data\WarehouseCustomerGroupInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseCustomerGroupInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the warehouse customer group: %1',
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
