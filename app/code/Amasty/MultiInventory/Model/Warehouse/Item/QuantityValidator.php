<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Item;

use Amasty\MultiInventory\Model\Config\Source\BackordersAction;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Helper\Data as StockHelper;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Math\Division as MathDivision;

class QuantityValidator
{
    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $warehouseStockRepository;

    /**
     * @var ValidatorResultDataFactory
     */
    private $resultDataFactory;

    /**
     * @var MathDivision
     */
    private $mathDivision;

    /**
     * QuantityValidator constructor.
     *
     * @param \Amasty\MultiInventory\Helper\Data                          $helper
     * @param \Amasty\MultiInventory\Helper\System                        $system
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $warehouseStockRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface        $stockRegistry
     * @param ValidatorResultDataFactory                                  $resultDataFactory
     * @param MathDivision                                                $mathDivision
     */
    public function __construct(
        \Amasty\MultiInventory\Helper\Data $helper,
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $warehouseStockRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Amasty\MultiInventory\Model\Warehouse\Item\ValidatorResultDataFactory $resultDataFactory,
        MathDivision $mathDivision
    ) {
        $this->helper = $helper;
        $this->system = $system;
        $this->stockRegistry = $stockRegistry;
        $this->warehouseStockRepository = $warehouseStockRepository;
        $this->resultDataFactory = $resultDataFactory;
        $this->mathDivision = $mathDivision;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function validate(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getItem();

        if (!$this->system->isMultiEnabled()
            || !$quoteItem
            || !$quoteItem->getProductId()
            || !$quoteItem->getQuote()
            || $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return;
        }

        $product = $quoteItem->getProduct();
        $stock = $this->stockRegistry->getStockItem(
            $product->getId(),
            $quoteItem->getStore()->getWebsiteId()
        );

        if (!$stock->getManageStock()) {
            return;
        }

        if ($stock->getMinSaleQty() && $quoteItem->getQty() < $stock->getMinSaleQty()) {
            return;
        }

        if ($stock->getSuppressCheckQtyIncrements()) {
            return;
        } else {
            $qtyIncrements = $stock->getQtyIncrements() * 1;
            if ($qtyIncrements && $this->mathDivision->getExactDivision($quoteItem->getQty(), $qtyIncrements) != 0) {
                return;
            }
        }

        if (!$quoteItem->getParentItem() && !$this->isProductSimple($quoteItem->getProduct())) {
            // remove errors for configurable product. Later will be checked for their children
            $this->removeErrorsFromQuoteAndItem($quoteItem, StockHelper::ERROR_QTY);
            return;
        }

        $this->helper->getDispatch()->setCallables($this->system->getDispatchOrder())
            ->resetExclude()
            ->setDirection(\Amasty\MultiInventory\Model\Dispatch::DIRECTION_QUOTE)
            ->setQuoteItem($quoteItem);

        $requestedQty = $quoteItem->getQty();

        if ($quoteItem->getParentItem()) {
            $requestedQty = $quoteItem->getParentItem()->getQty();
        }

        $this->checkQuoteItemQty($quoteItem, $requestedQty);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param int|null                        $requestedQty
     *
     * @return ValidatorResultData[]
     * @since 1.3.0 quantity validation and backorders status
     */
    public function checkQuoteItemQty($quoteItem, $requestedQty = null)
    {
        $product = $quoteItem->getProduct();
        if ($requestedQty === null) {
            $requestedQty = $quoteItem->getQty();
        }
        /** @var ValidatorResultData[] $results */
        $results = [];
        $dispatcher = $this->helper->getDispatch();
        $iteration = 0;
        do {
            $dispatcher->searchWh();
            $warehouse = $dispatcher->getWarehouse();

            if ($warehouse == $dispatcher->getDefaultWarehouseId() && $iteration > 0) {
                $this->notEnoughQty($quoteItem, $results, $requestedQty);
                break;
            }

            /** @var ValidatorResultData $result */
            $results[] = $result = $this->resultDataFactory->create()
                ->setQty($requestedQty)
                ->setProductId($product->getId())
                ->setWarehouseId($warehouse);

            if ($quoteItem instanceof \Magento\Quote\Model\Quote\Item) {
                $result->setQuoteId($quoteItem->getQuoteId())
                    ->setQuoteItemId($quoteItem->getItemId());
            } else {
                $result->setOrderId($quoteItem->getOrderId())
                    ->setOrderItemId($quoteItem->getItemId());
            }

            if ($iteration > 0) {
                $result->setIsSplitted(true);
            }

            $itemStock = $this->warehouseStockRepository->getByProductWarehouse($product->getId(), $warehouse);

            if ($itemStock->getStockStatus() == Stock::STOCK_OUT_OF_STOCK) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    StockHelper::ERROR_QTY,
                    __('This product is out of stock.')
                );
                $quoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    StockHelper::ERROR_QTY,
                    __('Some of the products are out of stock.')
                );
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->removeErrorsFromQuoteAndItem($quoteItem, StockHelper::ERROR_QTY);
                if ($quoteItem->getParentItem()) {
                    $this->removeErrorsFromQuoteAndItem($quoteItem->getParentItem(), StockHelper::ERROR_QTY);
                }
            }

            $warehouseQty = $itemStock->getRealQty();
            $stockFounded = true;
            // if not enough qty on one warehouse, then split item and take qty for multiple warehouses
            if ($warehouseQty < $requestedQty) {
                if ($this->system->getBackordersAction() == BackordersAction::DO_NOT_SPLIT
                    && $itemStock->isCanBackorder()
                ) {
                    if ($warehouseQty > 0) {
                        $requestedQty -= $warehouseQty;
                    }
                    $this->processQuoteBackorder($quoteItem, $itemStock, $requestedQty, $result);
                    break;
                }
                if ($warehouseQty > 0) {
                    $result->setIsChanged(true);
                    $stockFounded = false;
                    $requestedQty -= $warehouseQty;
                    $result->setQty($warehouseQty);
                    $dispatcher->addExclude($product->getId(), $warehouse);
                } elseif ($itemStock->isCanBackorder()) {
                    $this->processQuoteBackorder($quoteItem, $itemStock, $requestedQty, $result);
                    break;
                } else {
                    $this->processQuoteStockError($quoteItem, $product);
                    break;
                }
            }
            ++$iteration;
        } while ($stockFounded === false);

        return $results;
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     * @return bool
     */
    public function isProductSimple($item)
    {
        return $item->getTypeId() == Type::TYPE_SIMPLE;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param ValidatorResultData[] $results
     * @param int $requestedQty
     */
    private function notEnoughQty($quoteItem, $results, $requestedQty)
    {
        foreach ($results as $result) {
            $itemStock = $this->warehouseStockRepository
                ->getByProductWarehouse($quoteItem->getProduct()->getId(), $result->getWarehouseId());
            if ($itemStock->isCanBackorder()) {
                $result->setQty($result->getQty() + $requestedQty);
                $this->processQuoteBackorder($quoteItem, $itemStock, $requestedQty, $result);
                return;
            }
        }
        $this->processQuoteStockError($quoteItem, $quoteItem->getProduct());
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param \Amasty\MultiInventory\Model\Warehouse\Item                     $stock
     * @param int                                                             $backorderQty
     * @param ValidatorResultData                                             $result
     */
    private function processQuoteBackorder($quoteItem, $stock, $backorderQty, $result)
    {
        if ($quoteItem instanceof \Magento\Sales\Model\Order\Item) {
            $quoteItem->setQtyBackordered($backorderQty);
            return;
        }
        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }

        $result->setBackorderedQty($backorderQty);
        $quoteItem->setBackorders($backorderQty);

        if ($stock->isShowBackorderNotice()) {
            $backorderPhrase = __(
                'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                $quoteItem->getProduct()->getName(),
                $backorderQty
            );
            $quoteItem->addMessage($backorderPhrase);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param \Magento\Catalog\Model\Product $product
     */
    private function processQuoteStockError($quoteItem, $product)
    {
        $errorMessage = __('We don\'t have as many "%1" as you requested.', $product->getName());

        $quoteItem->addErrorInfo(
            'amasty_inventory',
            StockHelper::ERROR_QTY,
            $errorMessage
        );
        if ($quoteItem->getParentItem()) {
            $quoteItem->getParentItem()->addErrorInfo(
                'amasty_inventory',
                StockHelper::ERROR_QTY,
                $errorMessage
            );
        }
        $quoteItem->getQuote()->addErrorInfo(
            'amasty_inventory',
            StockHelper::ERROR_QTY,
            $errorMessage
        );
    }

    /**
     * Removes error statuses from quote and item, set by this observer
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $code
     * @return void
     */
    private function removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $item->removeErrorInfosByParams($params);
        }

        $quote = $item->getQuote();
        if (empty($quote)) {
            return;
        }
        $quoteItems = $quote->getItemsCollection();
        $canRemoveErrorFromQuote = true;

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getItemId() == $item->getItemId()) {
                continue;
            }

            $errorInfos = $quoteItem->getErrorInfos();
            foreach ($errorInfos as $errorInfo) {
                if ($errorInfo['code'] == $code) {
                    $canRemoveErrorFromQuote = false;
                    break;
                }
            }

            if (!$canRemoveErrorFromQuote) {
                break;
            }
        }

        if ($quote->getHasError() && $canRemoveErrorFromQuote) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $quote->removeErrorInfosByParams(null, $params);
        }

        $messages = $item->getMessage(false);
        if (is_array($messages)) {
            /** @var \Magento\Framework\Phrase $messagePhrase */
            foreach ($messages as $messagePhrase) {
                $text = $messagePhrase->getText();
                if (strpos($text, 'We don\'t have') !== false) {
                    $item->removeMessageByText($messagePhrase);
                    $item->removeErrorInfosByParams(['message' => $messagePhrase]);
                }
            }
        }

        $item->setUseOldQty(false);
    }
}
