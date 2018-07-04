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

class ImportRepository implements \Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import
     */
    protected $resource;

    /**
     * @var ImportFactory
     */
    protected $factory;

    /**
     * ImportRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import $resource
     * @param ImportFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import $resource,
        \Amasty\MultiInventory\Model\Warehouse\ImportFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param Data\WarehouseImportInterface $item
     * @return Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseImportInterface $item)
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
     * @return Import
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Import with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * @param Data\WarehouseImportInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseImportInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the warehouse import: %1',
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
