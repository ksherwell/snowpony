<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Item;

use Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface;
use Magento\Framework\DataObject;

class Api extends DataObject implements WarehouseItemApiInterface
{

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($data);
        $this->repository = $repository;
        $this->productRepository = $productRepository;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
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
     * @param $sku
     */
    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function setCode($code)
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * @param $qty
     * @return float
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);
    }

    /**
     * @param $qty
     * @return float
     */
    public function setAvailableQty($qty)
    {
        $this->setData(self::AVAILABLE_QTY, $qty);
    }

    /**
     * @param $qty
     * @return float
     */
    public function setShipQty($qty)
    {
        $this->setData(self::SHIP_QTY, $qty);
    }

    /**
     * @param $text
     * @return string
     */
    public function setRoomShelf($text)
    {
        $this->setData(self::ROOM_SHELF, $text);
    }

    /**
     * @return array
     */
    public function getItemData()
    {
        $data = $this->getData();
        $newData = [
            'warehouse_id' => $this->repository->getByCode($data['code'])->getId(),
            'product_id' => $this->productRepository->get($data['sku'])->getId(),
            'qty' => $data['qty']
        ];
        if (isset($data[self::SHIP_QTY])) {
            $newData[self::SHIP_QTY] = $data[self::SHIP_QTY];
        }
        if (isset($data[self::AVAILABLE_QTY])) {
            $newData[self::AVAILABLE_QTY] = $data[self::AVAILABLE_QTY];
        }
        if (isset($data[self::ROOM_SHELF])) {
            $newData[self::ROOM_SHELF] = $data[self::ROOM_SHELF];
        }
        return $newData;
    }
}
