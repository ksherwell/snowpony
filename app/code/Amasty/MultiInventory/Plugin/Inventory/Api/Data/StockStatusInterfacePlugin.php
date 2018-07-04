<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Api\Data;

use Amasty\MultiInventory\Model\Dispatch;

class StockStatusInterfacePlugin
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
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * StockStatusInterfacePlugin constructor.
     *
     * @param \Amasty\MultiInventory\Helper\System                        $system
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $warehouseStockRepository
     * @param Dispatch                                                    $dispatch
     * @param \Magento\Framework\DataObjectFactory                        $objectFactory
     * @param \Magento\Store\Api\StoreResolverInterface                   $storeResolver
     */
    public function __construct(
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $warehouseStockRepository,
        Dispatch $dispatch,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->system                   = $system;
        $this->warehouseStockRepository = $warehouseStockRepository;
        $this->dispatch                 = $dispatch;
        $this->objectFactory            = $objectFactory;
    }

    /**
     * Modify Stock Status
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject
     * @param callable $proceed
     *
     * @return bool
     */
    public function aroundGetStockStatus(
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject,
        callable $proceed
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $proceed();
        }
        $productId = $subject->getProductId();

        $stock = $this->getStock($productId);

        if (!$stock->getId()) {
            return $proceed();
        }

        return $stock->getStockStatus();
    }

    /**
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject
     * @param callable                                                $proceed
     *
     * @return int
     */
    public function aroundGetQty(
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject,
        callable $proceed
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $proceed();
        }
        $productId = $subject->getProductId();

        $stock = $this->getStock($productId);

        if (!$stock->getId()) {
            return $proceed();
        }

        return $stock->getRealQty();
    }

    /**
     * @param int $productId
     *
     * @return \Amasty\MultiInventory\Model\Warehouse\Item
     */
    private function getStock($productId)
    {
        if (!$this->system->isLockOnStore()) {
            return $this->warehouseStockRepository->getByProductWarehouse(
                $productId,
                $this->dispatch->getDefaultWarehouseId()
            );
        }

        $data = $this->objectFactory->create(
            [
                'data' => [
                    'product_id' => $productId
                ]
            ]
        );
        $this->dispatch->setCallables($this->system->getDispatchOrder());
        $this->dispatch->setData('object', $data);
        $this->dispatch->setDirection(Dispatch::DIRECTION_STORE);
        $this->dispatch->searchWh();
        $warehouse = $this->dispatch->getWarehouse();

        return $this->warehouseStockRepository->getByProductWarehouse($productId, $warehouse);
    }
}
