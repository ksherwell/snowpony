<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Quote;

use Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class Item extends AbstractWarehouse implements WarehouseQuoteItemInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item');
    }

    /**
     * Item constructor.
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @return int
     */
    public function getQuoteItemId()
    {
        return $this->getData(self::QUOTE_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ITEM_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteId($id)
    {
        $this->setData(self::QUOTE_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteItemId($id)
    {
        $this->setData(self::QUOTE_ITEM_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);

        return $this;
    }
}
