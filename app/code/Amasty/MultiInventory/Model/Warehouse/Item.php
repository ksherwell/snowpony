<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\Config\Source\Backorders;
use Amasty\MultiInventory\Model\Config\Source\BackordersDefault;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class Item extends AbstractWarehouse implements WarehouseItemInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item');
    }

    /**
     * Item constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Amasty\MultiInventory\Helper\System $system
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
        \Amasty\MultiInventory\Helper\System $system,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
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
        $this->system = $system;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Recalculate for Available Qty
     */
    public function recalcAvailable()
    {
        if ($this->system->getAvailableDecreese()) {
            $shipQty = max($this->getShipQty(), 0);
            $this->setAvailableQty($this->getQty() - $shipQty);
        } else {
            $this->setAvailableQty($this->getQty());
        }

        if ($this->_registry->registry('am_dont_recalc_availability')) {
            return $this;
        }

        if ($this->getRealQty() <= 0 && !$this->isCanBackorder()) {
            $this->setStockStatus(\Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK);
        } else {
            $this->setStockStatus(\Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK);
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getRealQty()
    {
        $qty = $this->getQty();
        $minQty = $this->stockRegistry->getStockItem($this->getProductId())->getMinQty();
        if ($this->system->getAvailableDecreese()) {
            $qty = $this->getAvailableQty();
        }

        return $qty - $minQty;
    }

    /**
     * @return bool
     */
    public function isCanBackorder()
    {
        return $this->getBackordersValue() > 0;
    }

    /**
     * Is can show the backorder notice
     */
    public function isShowBackorderNotice()
    {
        return $this->getBackordersValue() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY;
    }

    public function getItems($productId)
    {
        return $this->getResource()->getItems($productId);
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

    public function isStockStatusChanged()
    {
        if ($this->getOrigData('stock_status') != $this->getData('stock_status')) {
            return true;
        }

        if ($this->getData('available_qty') <= 0
            && $this->getOrigData('available_qty') > 0
        ) {
            return true;
        }

        if ($this->getData('available_qty') > 0
            && $this->getOrigData('available_qty') <= 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Warehouse item (warehouse product stock) have backorder configuration
     * which extend the Warehouse backorders configuration or product backorders configuration,
     * depends by system configuration
     *
     * @return int
     */
    public function getBackordersValue()
    {
        $bockorderValue = $this->getBackorders();
        if ($bockorderValue == Backorders::USE_CONFIG_OPTION_VALUE) {
            if ($this->system->getBackordersUseDefault() == BackordersDefault::USE_PRODUCT_BACKORDERS) {
                $bockorderValue = $this->stockRegistry->getStockItem($this->getProductId())->getBackorders();
            } else {
                $bockorderValue = $this->getWarehouse()->getBackorders();
            }
        }

        return $bockorderValue;
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
     * @return float
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * @return float
     */
    public function getAvailableQty()
    {
        return $this->getData(self::AVAILABLE_QTY);
    }

    /**
     * @return float
     */
    public function getShipQty()
    {
        return $this->getData(self::SHIP_QTY);
    }

    /**
     * @return string
     */
    public function getRoomShelf()
    {
        return $this->getData(self::ROOM_SHELF);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->setData(self::ITEM_ID, $id);
        return $this;
    }

    /**
     * @param $productWarehouse
     *
     * @return $this
     */
    public function setProductId($productWarehouse)
    {
        $this->setData(self::PRODUCT_ID, $productWarehouse);
        return $this;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);
        return $this;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setAvailableQty($qty)
    {
        $this->setData(self::AVAILABLE_QTY, $qty);
        return $this;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setShipQty($qty)
    {
        $this->setData(self::SHIP_QTY, $qty);
        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setRoomShelf($text)
    {
        $this->setData(self::ROOM_SHELF, $text);
        return $this;
    }

    /**
     * @return int
     */
    public function getStockStatus()
    {
        return $this->_getData(self::STOCK_STATUS);
    }

    /**
     * @param int $stockStatus
     *
     * @return $this
     */
    public function setStockStatus($stockStatus)
    {
        $this->setData(self::STOCK_STATUS, $stockStatus);
        return $this;
    }

    /**
     * @return int
     */
    public function getBackorders()
    {
        return $this->_getData(self::BACKORDERS);
    }

    /**
     * @param int $backorders
     *
     * @return $this
     */
    public function setBackorders($backorders)
    {
        $this->setData(self::BACKORDERS, $backorders);
        return $this;
    }
}
