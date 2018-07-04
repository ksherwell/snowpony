<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Api;

class StockStateInterfacePlugin
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $warehouseStockRepository;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\Cart
     */
    private $cart;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface
     */
    private $whQuoteItemRepository;

    public function __construct(
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $warehouseStockRepository,
        \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface $whQuoteItemRepository,
        \Amasty\MultiInventory\Model\Warehouse\Cart $cart
    ) {
        $this->system = $system;
        $this->warehouseStockRepository = $warehouseStockRepository;
        $this->cart = $cart;
        $this->whQuoteItemRepository = $whQuoteItemRepository;
    }

    /**
     * @param \Magento\CatalogInventory\Api\StockStateInterface $subject
     * @param callable                                          $proceed
     * @param int                                               $productId
     * @param float                                             $qty
     * @param int                                               $scopeId
     *
     * @return bool
     */
    public function aroundCheckQty(
        \Magento\CatalogInventory\Api\StockStateInterface $subject,
        callable $proceed,
        $productId,
        $qty,
        $scopeId = null
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $proceed($productId, $qty, $scopeId);
        }

        $result = true;
        $totalQty = 0;

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        foreach ($this->cart->getQuote()->getAllItems() as $quoteItem) {
            if ($quoteItem->getProductId() == $productId) {

                $warehouseId = $this->whQuoteItemRepository->getWarehouseIdByItem($quoteItem);
                if (!$warehouseId) {
                    break;
                }
                $stock = $this->warehouseStockRepository
                    ->getByProductWarehouse($quoteItem->getProductId(), $warehouseId);

                $result &= $stock->getRealQty() - $quoteItem->getQty() >= 0 || $stock->isCanBackorder();
                $totalQty += $quoteItem->getQty();
            }
        }
        if ($totalQty == $qty) {
            return (bool) $result;
        }

        return $proceed($productId, $qty, $scopeId);
    }
}