<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class CustomerGroup extends AbstractWarehouse implements WarehouseCustomerGroupInterface
{

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup');
    }

    /**
     * CustomerGroup constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
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
        \Magento\Customer\Model\GroupFactory $groupFactory,
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
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * @return $this
     */
    public function getCode()
    {
        return $this->groupFactory->create()->load($this->getGroupId())->getCustomerGroupCode();
    }

    /**
     * @param $id
     * @return int
     */
    public function setGroupId($id)
    {
        $this->setData(self::GROUP_ID, $id);
    }
}
