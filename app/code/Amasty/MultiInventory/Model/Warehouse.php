<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Magento\Framework\Model\Context;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

class Warehouse extends AbstractExtensibleModel implements WarehouseInterface
{
    /**
     * @var ResourceModel\Warehouse\CustomerGroup\CollectionFactory
     */
    private $collectionGroupFactory;

    /**
     * @var ResourceModel\Warehouse\Store\CollectionFactory
     */
    private $collectionStoresFactory;

    /**
     * @var ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $collectionItemsFactory;

    /**
     * @var ResourceModel\Warehouse\Shipping\CollectionFactory
     */
    private $collectionShippingsFactory;

    /**
     * @var \Amasty\MultiInventory\Logger\Logger
     */
    private $logger;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var Warehouse\ItemFactory
     */
    private $itemWarehouseFactory;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $whRepository;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse');
    }

    /**
     * Warehouse constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ResourceModel\Warehouse\CustomerGroup\CollectionFactory $collectionGroupFactory
     * @param ResourceModel\Warehouse\Store\CollectionFactory $collectionStoresFactory
     * @param ResourceModel\Warehouse\Item\CollectionFactory $collectionItemsFactory
     * @param ResourceModel\Warehouse\Shipping\CollectionFactory $collectionShippingsFactory
     * @param Warehouse\ItemFactory $itemWarehouseFactory
     * @param WarehouseItemRepositoryInterface $itemRepository
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $whRepository
     * @param \Amasty\MultiInventory\Logger\Logger $logger
     * @param \Amasty\MultiInventory\Helper\System $system
     * @param Processor $processor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup\CollectionFactory $collectionGroupFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store\CollectionFactory $collectionStoresFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $collectionItemsFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping\CollectionFactory $collectionShippingsFactory,
        Warehouse\ItemFactory $itemWarehouseFactory,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $whRepository,
        \Amasty\MultiInventory\Logger\Logger $logger,
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->collectionGroupFactory = $collectionGroupFactory;
        $this->collectionStoresFactory = $collectionStoresFactory;
        $this->collectionItemsFactory = $collectionItemsFactory;
        $this->collectionShippingsFactory = $collectionShippingsFactory;
        $this->logger = $logger;
        $this->system = $system;
        $this->itemWarehouseFactory = $itemWarehouseFactory;
        $this->itemRepository = $itemRepository;
        $this->whRepository = $whRepository;
        $this->processor = $processor;
    }

    /**
     * @return \Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface[]
     */
    public function getCustomerGroups()
    {
        if ($this->getData(self::CUSTOMER_GROUPS) == null) {
            $this->setData(
                self::CUSTOMER_GROUPS,
                $this->getGroupsCollection()->getItems()
            );
        }

        return $this->getData(self::CUSTOMER_GROUPS);
    }

    /**
     * @return mixed
     */
    public function getGroupIds()
    {
        return $this->getResource()->getGroupIds($this->getId());
    }

    /**
     * @return \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup\Collection
     */
    public function getGroupsCollection()
    {
        $collection = $this->collectionGroupFactory->create()->addFieldToFilter('warehouse_id', $this->getId());

        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }

        return $collection;
    }

    /**
     * @return \Amasty\MultiInventory\Api\Data\WarehouseStoreInterface[]
     */
    public function getStores()
    {
        if ($this->getData(self::STORES) == null) {
            $this->setData(
                self::STORES,
                $this->getStoresCollection()->getItems()
            );
        }
        return $this->getData(self::STORES);
    }

    /**
     * @return mixed
     */
    public function getStoreIds()
    {
        return $this->getResource()->getStoreIds($this->getId());
    }

    /**
     * @return \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store\Collection
     */
    public function getStoresCollection()
    {
        $collection = $this->collectionStoresFactory->create()->addFieldToFilter('warehouse_id', $this->getId());
        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }
        return $collection;
    }

    /**
     * @return \Amasty\MultiInventory\Api\Data\WarehouseShippingInterface[]
     */
    public function getShippings()
    {
        if ($this->getData(self::SHIPPINGS) == null) {
            $this->setData(
                self::SHIPPINGS,
                $this->getShippingsCollection()->getItems()
            );
        }
        return $this->getData(self::SHIPPINGS);
    }

    /**
     * @return mixed
     */
    public function getShippingsCodes()
    {
        return $this->getResource()->getShippingsCodes($this->getId());
    }

    /**
     * @return $this
     */
    public function getShippingsCollection()
    {
        $collection = $this->collectionShippingsFactory->create()->addFieldToFilter('warehouse_id', $this->getId());
        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }

        return $collection;
    }

    /**
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]
     */
    public function getItems()
    {
        if ($this->getData(self::ITEMS) === null) {
            $this->setData(
                self::ITEMS,
                $this->getItemsCollection()->getItems()
            );
        }

        return $this->getData(self::ITEMS);
    }

    /**
     * Items delete from warehouses
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]
     */
    public function getRemoveItems()
    {
        return $this->getData(self::REMOVE_ITEMS);
    }

    /**
     * @return mixed
     */
    public function getItemIds()
    {
        return $this->getResource()->getItemIds($this->getId());
    }

    /**
     * @return \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\Collection
     */
    public function getItemsCollection()
    {
        $collection = $this->collectionItemsFactory->create()->addFieldToFilter('warehouse_id', $this->getId());
        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function getProductsToGrid()
    {
        return $this->getResource()->getItemsToGrid($this);
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->getResource()->getItems($this);
    }

    /**
     * Count products
     *
     * @return int
     */
    public function getTotalSku()
    {
        return $this->getResource()->getTotalSku($this);
    }

    /**
     * Sum Qty for Warehouses
     *
     * @return int
     */
    public function getTotalQty($field = 'qty')
    {
        return $this->getResource()->getTotalQty($this, $field);
    }

    /**
     * General Sum from All Warehouses
     *
     * @param $productId
     * @return int
     */
    private function getAlltotalQty($productId)
    {
        return $this->getResource()->getAllTotalQty($productId, $this->getDefaultId());
    }

    /**
     * @return int
     */
    public function getDefaultId()
    {
        return $this->getResource()->getDefaultId(\Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID);
    }

    /**
     * Get is not actives Warehouses
     *
     * @return mixed
     */
    public function getWhNotActive()
    {
        return $this->getResource()->getWhNotActive(\Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID);
    }

    /**
     * Get codes Warehouses
     *
     * @return mixed
     */
    public function getWhCodes()
    {
        return $this->getResource()->getWhCodes(\Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID);
    }

    /**
     * @param $groups
     * @return $this
     */
    public function setCustomerGroups($groups)
    {
        return $this->setData(self::CUSTOMER_GROUPS, $groups);
    }

    /**
     * @param $stores
     * @return $this
     */
    public function setStores($stores)
    {
        return $this->setData(self::STORES, $stores);
    }

    /**
     * @param $shippings
     * @return $this
     */
    public function setShippings($shippings)
    {
        return $this->setData(self::SHIPPINGS, $shippings);
    }

    /**
     * @param $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @param $items
     * @return $this
     */
    public function setRemoveItems($items)
    {
        return $this->setData(self::REMOVE_ITEMS, $items);
    }

    /**
     * @param Warehouse\CustomerGroup $group
     * @return $this
     */
    public function addGroupCustomer(\Amasty\MultiInventory\Model\Warehouse\CustomerGroup $group)
    {
        $group->setWarehouse($this);
        if (!$group->getId()) {
            $this->setCustomerGroups(array_merge($this->getCustomerGroups(), [$group]));
        }

        return $this;
    }

    /**
     * @param Warehouse\Store $store
     * @return $this
     */
    public function addStore(\Amasty\MultiInventory\Model\Warehouse\Store $store)
    {
        $store->setWarehouse($this);
        if (!$store->getId()) {
            $this->setStores(array_merge($this->getStores(), [$store]));
        }

        return $this;
    }

    /**
     * @param Warehouse\Shipping $shipping
     * @return $this
     */
    public function addShippings(\Amasty\MultiInventory\Model\Warehouse\Shipping $shipping)
    {
        $shipping->setWarehouse($this);
        if (!$shipping->getId()) {
            $this->setShippings(array_merge($this->getShippings(), [$shipping]));
        }

        return $this;
    }

    /**
     * Add product for Warehouses
     *
     * @param Warehouse\Item $item
     * @return $this
     */
    public function addItem(\Amasty\MultiInventory\Model\Warehouse\Item $item)
    {
        $collection = $this->collectionItemsFactory->create()
            ->addFieldToFilter('warehouse_id', $this->getId())
            ->addFieldToFilter('product_id', $item->getProductId());
        // if product in warehouse
        if ($collection->getSize()) {
            $changeItem = $collection->getFirstItem();
            $oldQty = $changeItem->getQty();
            $changeItem->setData(array_merge($changeItem->getData(), $item->getData()))
                ->recalcAvailable()
                ->save();
            if ($this->system->isEnableLog()) {
                $this->logger->infoWh(
                    $changeItem->getProduct()->getSku(),
                    $changeItem->getProductId(),
                    $changeItem->getWarehouse()->getTitle(),
                    $changeItem->getWarehouse()->getCode(),
                    $oldQty,
                    $changeItem->getQty()
                );
            }
        } else {
            // if product is not in warehouse
            if (!$item->getId()) {
                $item->setWarehouse($this);
                $item->recalcAvailable();
                $item->save();
                if ($this->system->isEnableLog()) {
                    $this->logger->infoWh(
                        $item->getProduct()->getSku(),
                        $item->getProductId(),
                        $item->getWarehouse()->getTitle(),
                        $item->getWarehouse()->getCode(),
                        0,
                        $item->getQty(),
                        "null",
                        "null",
                        "true"
                    );
                }
            }
        }

        return $this;
    }

    /**
     * If on page Warehoses delete products
     *
     * @param Warehouse\Item $item
     * @return $this
     */
    public function addRemoveItem(\Amasty\MultiInventory\Model\Warehouse\Item $item)
    {
        $items = $this->getRemoveItems();
        if (!$items) {
            $this->setRemoveItems([$item]);
        } else {
            $this->setItems(array_merge($items, [$item]));
        }
        $item->setWarehouse($this);
        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $item->getProduct()->getSku(),
                $item->getProductId(),
                $item->getWarehouse()->getTitle(),
                $item->getWarehouse()->getCode(),
                $item->getQty(),
                0,
                "null",
                "null",
                "true"
            );
        }
        return $this;
    }

    /**
     * @param null $groupId
     * @return $this
     */
    public function deleteGroup($groupId = null)
    {
        $groups = $this->collectionGroupFactory->create()->addFieldToFilter('warehouse_id', $this->getId());

        if ($groupId) {
            $groups->addFieldToFilter('group_id', $groupId);
        }

        foreach ($groups as $item) {
            $item->delete();
        }

        $this->setData(
            self::CUSTOMER_GROUPS,
            $this->getGroupsCollection()->getItems()
        );
        return $this;
    }

    /**
     * @param null $storeId
     * @return $this
     */
    public function deleteStore($storeId = null)
    {
        $stores = $this->collectionStoresFactory->create()->addFieldToFilter('warehouse_id', $this->getId());

        if ($storeId) {
            $stores = $stores->addFieldToFilter('store_id', $storeId);
        }

        foreach ($stores as $item) {
            $item->delete();
        }
        $this->setData(
            self::STORES,
            $this->getStoresCollection()->getItems()
        );

        return $this;
    }

    /**
     * @param null $shippingCode
     * @return $this
     */
    public function deleteShipping($shippingCode = null)
    {
        $shippings = $this->collectionShippingsFactory->create()
            ->addFieldToFilter('warehouse_id', $this->getId());

        if ($shippingCode) {
            $shippings = $shippings->addFieldToFilter('shipping_method', $shippingCode);
        }

        foreach ($shippings as $item) {
            $item->delete();
        }
        $this->setData(
            self::SHIPPINGS,
            $this->getShippingsCollection()->getItems()
        );

        return $this;
    }

    /**
     * @param null $itemId
     * @return $this
     */
    public function deleteItems($itemId = null)
    {
        $items = $this->collectionItemsFactory->create()->addFieldToFilter('warehouse_id', $this->getId());

        if ($itemId) {
            $items->addFieldToFilter('product_id', $itemId);
        }

        foreach ($items as $item) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Recalculate for Total Stock from all warehouses
     *
     * Recalculate for Inventory
     */
    public function recalcInventory()
    {
        $whItems = $this->getItems();
        if (!$whItems) {
            $whItems = [];
        }
        $whRemItems = $this->getRemoveItems();
        if (!$whRemItems) {
            $whRemItems = [];
        }
        $items = array_merge($whItems, $whRemItems);
        $productIds = [];
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $productIds[] = $productId;
            if ($this->system->isEnableLog()) {
                $totalItem = $this->createDefaultStock($item->getProductId());
                $oldQty = $totalItem->getQty();
                $newQty = $this->getAlltotalQty($productId);

                $this->logger->infoWh(
                    $totalItem->getProduct()->getSku(),
                    $productId,
                    $totalItem->getWarehouse()->getTitle(),
                    $totalItem->getWarehouse()->getCode(),
                    $oldQty,
                    $newQty
                );
            }
        }

        if (count($productIds)) {
            $this->processor->reindexList($productIds);
        }
    }

    /**
     * @param $productId
     * @return \Magento\Framework\DataObject|mixed|null
     */
    private function createDefaultStock($productId)
    {
        $totalItem = null;
        $totalStock = $this->collectionItemsFactory->create()
            ->addFieldToFilter('warehouse_id', $this->getDefaultId())
            ->addFieldToFilter('product_id', $productId);
        if ($totalStock->getSize()) {
            $totalItem = $totalStock->getFirstItem();
        } else {
            $object = $this->itemWarehouseFactory->create();
            $warehouse = $this->whRepository->getById($this->getDefaultId());
            $object->setWarehouse($warehouse);
            $object->setProductId($productId);
            $object->setQty(0);
            $this->itemRepository->save($object);
            $totalItem = $object;
        }

        return $totalItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_getData(WarehouseInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData(WarehouseInterface::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_getData(WarehouseInterface::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->setData(WarehouseInterface::CODE, $code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry()
    {
        return $this->_getData(WarehouseInterface::COUNTRY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountry($country)
    {
        $this->setData(WarehouseInterface::COUNTRY, $country);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->_getData(WarehouseInterface::STATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->setData(WarehouseInterface::STATE, $state);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_getData(WarehouseInterface::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        $this->setData(WarehouseInterface::CITY, $city);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->_getData(WarehouseInterface::ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress($address)
    {
        $this->setData(WarehouseInterface::ADDRESS, $address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getZip()
    {
        return $this->_getData(WarehouseInterface::ZIP);
    }

    /**
     * {@inheritdoc}
     */
    public function setZip($zip)
    {
        $this->setData(WarehouseInterface::ZIP, $zip);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone()
    {
        return $this->_getData(WarehouseInterface::PHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPhone($phone)
    {
        $this->setData(WarehouseInterface::PHONE, $phone);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_getData(WarehouseInterface::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->setData(WarehouseInterface::EMAIL, $email);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_getData(WarehouseInterface::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->setData(WarehouseInterface::DESCRIPTION, $description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getManage()
    {
        return $this->_getData(WarehouseInterface::MANAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setManage($manage)
    {
        $this->setData(WarehouseInterface::MANAGE, $manage);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->_getData(WarehouseInterface::PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        $this->setData(WarehouseInterface::PRIORITY, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsGeneral()
    {
        return $this->_getData(WarehouseInterface::IS_GENERAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGeneral($isGeneral)
    {
        $this->setData(WarehouseInterface::IS_GENERAL, $isGeneral);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderEmailNotification()
    {
        return $this->_getData(WarehouseInterface::ORDER_EMAIL_NOTIFICATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderEmailNotification($orderEmailNotification)
    {
        $this->setData(WarehouseInterface::ORDER_EMAIL_NOTIFICATION, $orderEmailNotification);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLowStockNotification()
    {
        return $this->_getData(WarehouseInterface::LOW_STOCK_NOTIFICATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setLowStockNotification($lowStockNotification)
    {
        $this->setData(WarehouseInterface::LOW_STOCK_NOTIFICATION, $lowStockNotification);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(WarehouseInterface::STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId)
    {
        $this->setData(WarehouseInterface::STOCK_ID, $stockId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateTime()
    {
        return $this->_getData(WarehouseInterface::CREATE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreateTime($createTime)
    {
        $this->setData(WarehouseInterface::CREATE_TIME, $createTime);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateTime()
    {
        return $this->_getData(WarehouseInterface::UPDATE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdateTime($updateTime)
    {
        $this->setData(WarehouseInterface::UPDATE_TIME, $updateTime);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsShipping()
    {
        return $this->_getData(WarehouseInterface::IS_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsShipping($isShipping)
    {
        $this->setData(WarehouseInterface::IS_SHIPPING, $isShipping);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackorders()
    {
        return $this->_getData(WarehouseInterface::BACKORDERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackorders($backorders)
    {
        $this->setData(WarehouseInterface::BACKORDERS, $backorders);

        return $this;
    }
}
