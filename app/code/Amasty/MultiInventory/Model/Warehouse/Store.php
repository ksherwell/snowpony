<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseStoreInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class Store extends AbstractWarehouse implements WarehouseStoreInterface
{

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store');
    }

    /**
     * Store constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
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
        $this->warehouseFactory = $warehouseFactory;
        $this->storeFactory = $storeFactory;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->storeFactory->create()->load($this->getStoreId());
    }

    /**
     * @param $id
     * @return int
     */
    public function setStoreId($id)
    {
        $this->setData(self::STORE_ID, $id);
    }
}
