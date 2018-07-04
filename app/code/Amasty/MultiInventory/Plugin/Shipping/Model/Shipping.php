<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Plugin\Shipping\Model;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;

class Shipping
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
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repostiory;

    /**
     * @var \Amasty\MultiInventory\Helper\Cart
     */
    private $helperCart;

    /**
     * @var \Amasty\MultiInventory\Model\ShippingFactory
     */
    private $whShipping;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $manager;

    /**
     * Shipping constructor.
     * @param Warehouse\Cart $cart
     * @param WarehouseFactory $factory
     * @param \Amasty\MultiInventory\Helper\System $system
     * @param \Amasty\MultiInventory\Helper\Cart $helperCart
     * @param \Magento\Framework\Registry $registry
     * @param \Amasty\MultiInventory\Model\ShippingFactory $whShipping
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Amasty\MultiInventory\Model\Warehouse\Cart $cart,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Helper\Cart $helperCart,
        \Magento\Framework\Registry $registry,
        \Amasty\MultiInventory\Model\ShippingFactory $whShipping,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Module\Manager $manager
    ) {
        $this->cart = $cart;
        $this->factory = $factory;
        $this->repostiory = $repository;
        $this->system = $system;
        $this->helperCart = $helperCart;
        $this->whShipping = $whShipping;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->manager = $manager;
    }

    /**
     * Separate rates, if some wearehouses
     *
     * @param \Magento\Shipping\Model\Shipping $shipping
     * @param \Closure $work
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Shipping\Model\Shipping
     */
    public function aroundCollectRates(
        \Magento\Shipping\Model\Shipping $shipping,
        \Closure $work,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        if (!$this->system->isMultiEnabled() || !$this->system->getDefinationWarehouse()) {
            return $work($request);
        }
        $oldQuoteItems = [];
        $quoteItem = current($request->getAllItems());
        if ($quoteItem instanceof \Magento\Quote\Model\Quote\Item) {
            $this->cart->setQuote($quoteItem->getQuote());
        }

        if ($this->registry->registry('finish_quote_save') !== true) {
            $this->cart->addWhToItems();
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($this->cart->getQuote()->getItemsCollection()->getItems() as $item) {
                if ($item->getId()) {
                    $oldQuoteItems[$item->getId()] = $item->getQty();
                }
            }
        }

        $warehouses = $this->cart->getWarehouses();
        $result = $forShipResult = [];

        foreach ($warehouses as $warehouseId) {
            $warehouse = $this->repostiory->getById($warehouseId);
            $request = $this->helperCart->changeRequestAddress(
                $request,
                $warehouse->getData()
            );
            // get all cart items of current warehouse
            $items = $this->cart->getGroupItems($warehouseId);
            $groupItems = $addedParents = [];
            foreach ($items as $item) {
                $quoteItem = $this->cart->getQuote()->getItemById($item['quote_item_id']);
                if ($this->registry->registry('finish_quote_save') !== true) {
                    $quoteItem->setData(Item::KEY_QTY, $item['qty']);
                }

                $parentId = $quoteItem->getParentItemId();
                if ($parentId) {
                    $parentItem = $this->cart->getQuote()->getItemById($quoteItem->getParentItemId());
                    if (!in_array($parentId, $addedParents)) {
                        $addedParents[] = $parentId;
                        $groupItems[] = $parentItem;
                    }
                    if ($parentItem->getProductType() == 'bundle') {
                        continue;
                    }
                }

                $groupItems[] = $quoteItem;
            }
            $request  = $this->helperCart->changeRequestItems($request, $groupItems, $this->cart->getQuote());
            $shipment = $this->shipmentCalculate($request, $work);
            if ($warehouse->getIsShipping()) {
                $shipment = $this->helperCart->changePrice($shipment, $warehouse);
            }

            $result[$warehouseId] = $shipment;
            $methods = [];
            foreach ($shipment->getAllRates() as $resultMethod) {
                $methods[] = [
                    'method' => $resultMethod->getMethod(),
                    'carrier_code' => $resultMethod->getCarrier(),
                    'price' => $resultMethod->getPrice()
                ];
            }

            $forShipResult[$warehouseId] = $methods;
        }
        $this->registry->unregister('amasty_quote_methods');
        $this->registry->register('amasty_quote_methods', $forShipResult);
        $shipping->getResult()->append($this->helperCart->sumShipping($result));
        foreach ($oldQuoteItems as $key => $qty) {
            $quoteItem = $this->cart->getQuote()->getItemById($key);
            if ($quoteItem) {
                $quoteItem->setData(Item::KEY_QTY, $qty);
            }
        }
        $this->registry->unregister('finish_quote_save');
        $this->registry->register('finish_quote_save', false);

        return $shipping;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Closure                                       $work
     *
     * @return \Magento\Shipping\Model\Rate\Result
     */
    private function shipmentCalculate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $work)
    {
        $storeId = $request->getStoreId();
        /** @var \Amasty\MultiInventory\Model\Shipping $whShipping */
        $whShipping = $this->whShipping->create();
        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            if (!$this->manager->isEnabled('Amasty_Shiprules')) {
                foreach ($this->getCarriers($storeId) as $carrierCode => $carrierConfig) {
                    $whShipping->collectCarrierRates($carrierCode, $request);
                }
            } elseif (!$this->registry->registry('is_shipping_rules')) {
                $work($request);
                $this->registry->register('is_shipping_rules', true);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = [$limitCarrier];
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = $this->getCarriers($storeId, $carrierCode);
                if (!$carrierConfig) {
                    continue;
                }

                $whShipping->collectCarrierRates($carrierCode, $request);
            }
        }

        return $whShipping->getResult();
    }

    /**
     * @param int         $storeId
     * @param string|null $carrierCode
     *
     * @return array
     */
    private function getCarriers($storeId, $carrierCode = null)
    {
        $configPath = 'carriers';
        if ($carrierCode !== null) {
            $configPath .= '/' . $carrierCode;
        }
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
