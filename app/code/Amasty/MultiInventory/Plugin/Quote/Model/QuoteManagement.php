<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Quote\Model;

use Amasty\MultiInventory\Model\Warehouse\QuoteItemRepository;
use Magento\Catalog\Model\Product\Type;

class QuoteManagement
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\Cart
     */
    private $cart;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var QuoteItemRepository
     */
    private $itemQuoteRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item
     */
    private $itemResource;

    /**
     * QuoteManagement constructor.
     *
     * @param \Amasty\MultiInventory\Model\Warehouse\Cart                                       $cart
     * @param \Amasty\MultiInventory\Helper\System                                              $system
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item                   $itemResource
     * @param QuoteItemRepository                                                               $itemQuoteRepository
     * @param \Magento\Framework\Registry                                                       $registry
     * @param \Magento\Framework\DataObjectFactory                                              $objectFactory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\Warehouse\Cart $cart,
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item $itemResource,
        \Amasty\MultiInventory\Model\Warehouse\QuoteItemRepository $itemQuoteRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->cart                  = $cart;
        $this->itemQuoteRepository   = $itemQuoteRepository;
        $this->system                = $system;
        $this->registry              = $registry;
        $this->objectFactory         = $objectFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->itemResource          = $itemResource;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $object
     * @param \Closure                             $work
     * @param \Magento\Quote\Model\Quote           $quote
     * @param array                                $orderData
     *
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object|null
     */
    public function aroundSubmit(
        \Magento\Quote\Model\QuoteManagement $object,
        \Closure $work,
        \Magento\Quote\Model\Quote $quote,
        $orderData = []
    ) {
        if ($this->system->isMultiEnabled() && $this->system->getDefinationWarehouse()) {
            $this->cart->getCheckoutCart()->setQuote($quote);
            $items = $this->itemResource->getCountOfItems($this->cart->getQuote()->getId());
            if ($items) {
                $this->separateItems($items);
                $this->save();
            }
        }

        return $work($quote, $orderData);
    }

    /**
     * Add to cart slitted items before order place
     *
     * @param array $items
     */
    private function separateItems($items)
    {
        $processedItems = [];
        foreach (array_keys($items) as $itemId) {
            $quoteItem = $this->cart->getQuote()->getItemById($itemId);

            /** @var \Amasty\MultiInventory\Model\Warehouse\Quote\Item[] $amItems */
            $amItems = $this->itemCollectionFactory->create()
                ->addFieldToFilter('quote_item_id', $itemId)
                ->getItems();
            $count = 1;
            foreach ($amItems as $item) {
                if ($count == 1) {
                    if ($quoteItem->getParentItem()) {
                        $quoteItem->getParentItem()->setQty($item->getQty());
                        $processedItems[] = $quoteItem->getParentItemId();
                    } else {
                        $quoteItem->setQty($item->getQty());
                    }
                    $processedItems[] = $quoteItem->getId();
                } else {
                    $info = $quoteItem->getBuyRequest();
                    $product = $quoteItem->getProduct();
                    if ($quoteItem->getParentItem()) {
                        $product = $quoteItem->getParentItem()->getProduct();
                        $info = $quoteItem->getParentItem()->getBuyRequest();
                    }
                    $info->setQty($item->getQty());
                    $info->setOriginalQty($item->getQty());
                    $info->unsetData('uenc');
                    /** @var \Magento\Quote\Model\Quote\Item $newQuoteItem */
                    $newQuoteItem = $this->cart->addProduct($product, $info);
                    $newQuoteItem->setProduct($product);
                    $newQuoteItem->save();

                    $processedItems[] = $newQuoteItem->getId();
                    $optionAdded = false;
                    if ($newQuoteItem->getHasChildren()) {
                        /** @var \Magento\Quote\Model\Quote\Item $newQuoteChildrens */
                        foreach ($newQuoteItem->getChildren() as $newQuoteChildrens) {
                            $newQuoteChildrens->save();
                            $processedItems[] = $newQuoteChildrens->getId();
                            if ($quoteItem->getProduct()->getId() == $newQuoteChildrens->getProduct()->getId()) {
                                $item->setQuoteItemId($newQuoteChildrens->getId());
                                $optionAdded = true;
                                break;
                            }
                        }
                    }
                    if (!$optionAdded) {
                        $item->setQuoteItemId($newQuoteItem->getId());
                    }
                    $this->itemQuoteRepository->save($item);
                }
                $count++;
            }
        }
    }

    /**
     * Save Quote
     */
    private function save()
    {
        $this->cart->getQuote()->setTotalsCollectedFlag(false);
        $this->registry->unregister('finish_quote_save');
        $this->registry->register('finish_quote_save', true);
        $this->cart->getQuote()->getShippingAddress()->unsetData('cached_items_all');
        $this->cart->getCheckoutCart()->unsetData('items_collection');
        $this->cart->getQuote()->unsetData('items_collection');
        $this->cart->getCheckoutCart()->save();
    }
}
