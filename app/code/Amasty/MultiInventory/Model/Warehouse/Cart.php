<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Json\DecoderInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Session;

class Cart extends \Magento\Framework\DataObject
{
    /**
     * @var Quote\ItemFactory
     */
    private $quoteItemWhFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface
     */
    private $quoteItemWhRepository;

    /**
     * @var \Amasty\MultiInventory\Model\Dispatch
     */
    private $dispatch;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $checkoutCart;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Processor
     */
    private $itemProcessor;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $stockCollection;
    /**
     * @var ItemRepository
     */
    private $stockRepository;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory
     */
    private $quoteItemWhCollection;

    /**
     * @var Item\QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $backendQuote;

    /**
     * Cart constructor.
     *
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory       $itemCollection
     * @param ItemRepository                                                                    $stockRepository
     * @param Quote\ItemFactory                                                                 $quoteItemWhFactory
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $quoteItemWhCollection
     * @param \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface                  $quoteItemWhRepository
     * @param \Amasty\MultiInventory\Helper\System                                              $system
     * @param \Magento\Checkout\Model\Cart                                                      $checkoutCart
     * @param \Amasty\MultiInventory\Model\Dispatch                                             $dispatch
     * @param \Magento\Quote\Model\Quote\Item\Processor                                         $itemProcessor
     * @param \Magento\Framework\Event\ManagerInterface                                         $eventManager
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory            $quoteOptionFactory
     * @param array                                                                             $data
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $itemCollection,
        \Amasty\MultiInventory\Model\Warehouse\ItemRepository $stockRepository,
        \Amasty\MultiInventory\Model\Warehouse\Quote\ItemFactory $quoteItemWhFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $quoteItemWhCollection,
        \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface $quoteItemWhRepository,
        \Amasty\MultiInventory\Helper\System $system,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Amasty\MultiInventory\Model\Dispatch $dispatch,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator $quantityValidator,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\Quote\Proxy $backendQuote,
        array $data = []
    ) {
        parent::__construct($data);
        $this->quoteItemWhFactory    = $quoteItemWhFactory;
        $this->quoteItemWhRepository = $quoteItemWhRepository;
        $this->checkoutCart          = $checkoutCart;
        $this->dispatch              = $dispatch;
        $this->system                = $system;
        $this->itemProcessor         = $itemProcessor;
        $this->eventManager          = $eventManager;
        $this->stockCollection       = $itemCollection;
        $this->stockRepository       = $stockRepository;
        $this->quoteItemWhCollection = $quoteItemWhCollection;
        $this->quantityValidator = $quantityValidator;
        $this->appState = $appState;
        $this->backendQuote = $backendQuote;
    }

    /**
     * @return \Amasty\MultiInventory\Model\Dispatch
     */
    public function getDispatch()
    {
        if (!$this->hasData('dispatch')) {
            $this->setData('dispatch', $this->dispatch);
        }

        return $this->_getData('dispatch');
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $quote = $this->backendQuote->getQuote();
            } else {
                $quote = $this->getCheckoutCart()->getQuote();
            }
            $this->setData('quote', $quote);
        }

        return $this->_getData('quote');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->setData('quote', $quote);

        return $this;
    }

    /**
     * @param int $warehouseId
     *
     * @return array
     */
    public function getGroupItems($warehouseId)
    {
        return $this->getItems()->addFieldToFilter('warehouse_id', $warehouseId)->getData();
    }

    /**
     * get involved warehouse ids
     *
     * @return array
     */
    public function getWarehouses()
    {
        $warehouses = [];

        $itemsCollection = $this->getItems();
        $itemsCollection->getSelect()->group('warehouse_id');
        foreach ($itemsCollection->getData() as $itemData) {
            $warehouses[] = $itemData['warehouse_id'];
        }

        return $warehouses;
    }

    /**
     * @return \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\Collection
     */
    private function getItems()
    {
        return $this->quoteItemWhCollection->create()
            ->addFieldToFilter('quote_id', $this->getQuote()->getId());
    }

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    public function getCheckoutCart()
    {
        return $this->checkoutCart;
    }

    /**
     * @return $this
     */
    public function clearItems()
    {
        $collection = $this->getItems();
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $this->quoteItemWhRepository->delete($item);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addWhToItems()
    {
        if ($this->getQuote()->getItemsCount()) {
            $quoteItems = $this->getQuote()->getItemsCollection()->getItems();
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            foreach ($quoteItems as $quoteItem) {
                if ($quoteItem->getId() && !$quoteItem->isDeleted()) {
                    $this->addWhToQuote($quoteItem);
                }
            }
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     */
    public function addWhToQuote($quoteItem)
    {
        if ($result = $this->dispatchWarehouse($quoteItem)) {
            foreach ($result as $item) {
                $quoteWhItem = $this->quoteItemWhFactory->create();
                $quoteWhItem->addData($item->getData());
                $this->saveQuoteItemWarehouse($quoteWhItem);
            }
        }
    }

    /**
     * @param Quote\Item $quoteWhItemToSave
     */
    private function saveQuoteItemWarehouse($quoteWhItemToSave)
    {
        /** @var Quote\Item $quoteWhItem */
        $quoteWhItem = $this->quoteItemWhCollection->create()
            ->addFieldToFilter('quote_item_id', $quoteWhItemToSave->getQuoteItemId())
            ->addFieldToFilter('warehouse_id', $quoteWhItemToSave->getWarehouseId())
            ->setPageSize(1)
            ->getFirstItem();

        if (!$quoteWhItem->isObjectNew()) {
            $quoteWhItemToSave->setId($quoteWhItem->getId());
        }

        $this->quoteItemWhRepository->save($quoteWhItemToSave);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return \Amasty\MultiInventory\Model\Warehouse\Item\ValidatorResultData[]
     */
    public function dispatchWarehouse($quoteItem)
    {
        $result = [];
        $this->getDispatch()->setCallables($this->system->getDispatchOrder());
        $this->getDispatch()->resetExclude();
        if ($quoteItem->getParentItemId() || $this->quantityValidator->isProductSimple($quoteItem->getProduct())) {
            $this->prepareDispatch($quoteItem);
            $qty = $quoteItem->getQty();
            if ($quoteItem->getParentItem()) {
                $qty = $quoteItem->getParentItem()->getQty();
            }
            $checkResult = $this->quantityValidator->checkQuoteItemQty($quoteItem, $qty);
            $result = array_merge($result, $checkResult);
        }

        return $result;
    }

    /**
     * Set Dispatch Config
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    private function prepareDispatch($item)
    {
        $dispatch = $this->getDispatch();
        $dispatch->setQuoteItem($item);
        $dispatch->setDirection(\Amasty\MultiInventory\Model\Dispatch::DIRECTION_QUOTE);
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
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject|null $request
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addProduct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $request = null
    ) {
        if (!$request instanceof \Magento\Framework\DataObject) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid product for adding product to quote.')
            );
        }
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL;
        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $parentItem = null;
        $errors = [];
        $item = null;
        $items = [];

        foreach ($cartCandidates as $candidate) {
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->itemProcessor->init($candidate, $request);
            $item->setQuote($this->getQuote());
            $item->setOptions($candidate->getCustomOptions());
            $item->setProduct($candidate);
            $this->getQuote()->addItem($item);
            $items[] = $item;
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }
            $this->itemProcessor->prepare($item, $request, $candidate);
            if ($item->getHasError()) {
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        $errors[] = $message;
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
        }

        $this->eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);

        return $parentItem;
    }
}
