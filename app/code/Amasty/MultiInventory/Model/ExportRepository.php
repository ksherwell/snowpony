<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class ExportRepository implements \Amasty\MultiInventory\Api\ExportRepositoryInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Export
     */
    protected $resource;

    /**
     * @var ExportFactory
     */
    protected $factory;

    /**
     * ExportRepository constructor.
     * @param \Amasty\MultiInventory\Model\ResourceModel\Export $resource
     * @param ExportFactory $factory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Export $resource,
        \Amasty\MultiInventory\Model\ExportFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param Data\ExportInterface $item
     * @return Data\ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\ExportInterface $item)
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
     * @return Export
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Export with id %1 does not exist.', $id));
        }

        return $model;
    }

    /**
     * @param Data\ExportInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ExportInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Export: %1',
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
