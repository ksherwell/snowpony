<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\ConfigurableProduct\Model\Product\Type;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ParentConfigurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse;
use Amasty\MultiInventory\Helper\System;

class Configurable
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Warehouse
     */
    private $warehouse;

    /**
     * @var System
     */
    private $system;

    /**
     * Configurable constructor.
     * @param StoreManagerInterface $storeManager
     * @param Warehouse $warehouse
     * @param System $system
     */
    public function __construct(StoreManagerInterface $storeManager, Warehouse $warehouse, System $system)
    {
        $this->storeManager = $storeManager;
        $this->warehouse = $warehouse;
        $this->system = $system;
    }

    /**
     * Set correct simple products for configurable, when simples are manages by store
     * @param ParentConfigurable $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetUsedProductCollection(ParentConfigurable $subject, Collection $result)
    {
        if ($this->system->isLockOnStore() && $this->system->isMultiEnabled()) {
            $storeId = $this->storeManager->getStore()->getId();
            $warehouses = $this->warehouse->getWarehousesByStoreId($storeId);
            $result->getSelect()->join(['stockitem' => $result->getTable(Warehouse::AMASTY_INVENTORY_ITEM)],
                "e.entity_id = stockitem.product_id",
                ['warehouse_id']);
            $result->getSelect()->where('stockitem.warehouse_id IN (?)', $warehouses);
        }

        return $result;
    }
}
