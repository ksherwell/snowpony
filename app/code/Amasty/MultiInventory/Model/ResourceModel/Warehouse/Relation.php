<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class Relation
 */
class Relation implements RelationInterface
{
    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseCustomerGroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseStoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseShippingRepositoryInterface
     */
    private $shippingRepository;

    /**
     * Relation constructor.
     * @param \Amasty\MultiInventory\Api\WarehouseCustomerGroupRepositoryInterface $customerGroupRepository
     * @param \Amasty\MultiInventory\Api\WarehouseStoreRepositoryInterface $storeRepository
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository
     * @param \Amasty\MultiInventory\Api\WarehouseShippingRepositoryInterface $shippingRepository
     */
    public function __construct(
        \Amasty\MultiInventory\Api\WarehouseCustomerGroupRepositoryInterface $customerGroupRepository,
        \Amasty\MultiInventory\Api\WarehouseStoreRepositoryInterface $storeRepository,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository,
        \Amasty\MultiInventory\Api\WarehouseShippingRepositoryInterface $shippingRepository
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->itemRepository = $itemRepository;
        $this->storeRepository = $storeRepository;
        $this->shippingRepository = $shippingRepository;
    }

    /**
     * Save relations for Warehouse
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Exception
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        if (null !== $object->getShippings()) {
            foreach ($object->getShippings() as $key => $item) {
                if (!$item->getWarehouseId()) {
                    $item->setWarehouse($object);
                }
                $this->shippingRepository->save($item);
            }
        }

        if (null !== $object->getCustomerGroups()) {
            foreach ($object->getCustomerGroups() as $group) {
                if (!$group->getWarehouseId()) {
                    $group->setWarehouse($object);
                }
                $this->customerGroupRepository->save($group);
            }
        }
        if (null !== $object->getStores()) {
            foreach ($object->getStores() as $store) {
                if (!$store->getWarehouseId()) {
                    $store->setWarehouse($object);
                }
                $this->storeRepository->save($store);
            }
        }
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $key => $item) {
                if (!$item->getWarehouseId()) {
                    $item->setWarehouse($object);
                }
                $this->itemRepository->save($item);
            }
        }
        if (!$object->getIsGeneral()) {
            $object->recalcInventory();
        }
    }
}
