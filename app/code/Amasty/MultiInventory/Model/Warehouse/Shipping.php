<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseShippingInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class Shipping extends AbstractWarehouse implements WarehouseShippingInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping');
    }

    /**
     * Shipping constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
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
    }

    /**
     * @return mixed
     */
   public function getShippingMethod()
   {
       return $this->getData(self::SHIPPING_METHOD);
   }

    /**
     * @return mixed
     */
   public function getRate()
   {
       return $this->getData(self::RATE);
   }

    /**
     * @param $method
     */
   public function setShippingMethod($method)
   {
       $this->setData(self::SHIPPING_METHOD, $method);
   }

    /**
     * @param $rate
     */
   public function setRate($rate)
   {
       $this->setData(self::RATE, $rate);
   }
}
