<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseImportInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class Import extends AbstractWarehouse implements WarehouseImportInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import');
    }

    /**
     * Import constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
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
        $this->productRepository = $productRepository;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($this->product == null) {
            $this->product = $this->productRepository->getById($this->getProductId());
        }

        return $this->product;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * @return float
     */
    public function getNewQty()
    {
        return $this->getData(self::NEW_QTY);
    }

    /**
     * @return int
     */
    public function getImportNumber()
    {
        return $this->getData(self::IMPORT_NUMBER);
    }

    /**
     * @param $id
     */
    public function setProductId($id)
    {
        $this->setData(self::PRODUCT_ID, $id);
    }

    /**
     * @param $qty
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);
    }

    /**
     * @param $qty
     */
    public function setNewQty($qty)
    {
        $this->setData(self::NEW_QTY, $qty);
    }

    /**
     * @param $number
     */
    public function setImportNumber($number)
    {
        $this->setData(self::IMPORT_NUMBER, $number);
    }
}
