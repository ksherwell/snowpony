<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Order;

class Processor
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
     * @var \Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    public function __construct(
        \Amasty\MultiInventory\Helper\Data $helper,
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator $quantityValidator,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->helper = $helper;
        $this->system = $system;
        $this->quantityValidator = $quantityValidator;
        $this->orderRepository = $orderRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->priceCurrency = $priceCurrency;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Get warehouse for order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function dispatchWarehouse($order)
    {
        $this->helper->getDispatch()->setCallables($this->system->getDispatchOrder());
        $this->helper->getDispatch()->resetExclude();
        $this->helper->getDispatch()->setDirection(\Amasty\MultiInventory\Model\Dispatch::DIRECTION_ORDER);

        $results = [];
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getParentItemId() || $this->quantityValidator->isProductSimple($orderItem->getProduct())) {
                $this->helper->getDispatch()->setOrderItem($orderItem);
                $validationResult = $this->quantityValidator
                    ->checkQuoteItemQty($orderItem, $orderItem->getQtyOrdered());

                $itemResults = $this->checkOrderItem($validationResult, $orderItem);
                $results = array_merge($results, $itemResults);
            }
        }

        return $results;
    }

    /**
     * Separate orders on warehouses
     *
     * @param $result
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function separateOrders($result, $order)
    {
        if (!$this->system->getSeparateOrders()) {
            return [$result, [$order]];
        }

        $list = [];

        foreach ($result as $key => $itemEntity) {
            if (!isset($list[$itemEntity['warehouse_id']])) {
                $list[$itemEntity['warehouse_id']] = [];
            }
            $list[$itemEntity['warehouse_id']][] = $key;
        }

        if (count($list) <= 1) {
            return [$result, [$order]];
        }

        $orders = [];
        $numberOrder = 1;
        $baseShippingAmount = $order->getBaseShippingAmount();
        if ($baseShippingAmount) {
            $baseShippingAmount = round($order->getBaseShippingAmount() / count($list), 4);
        }
        foreach ($list as $wh => $items) {
            if ($numberOrder > 1) {
                $newOrder = $this->orderFactory->create();
                $newOrder->setData($this->beforeDataOrder($order->getData()));
                $payment = $order->getPayment();
                $payment->setId(null);
                $payment->setParentId(null);
                $newOrder->setPayment($payment);

                $addresses = $order->getAddresses();
                foreach ($addresses as $address) {
                    $address->setId(null);
                    $address->setParentId(null);
                    $newOrder->addAddress($address);
                }

                $this->orderRepository->save($newOrder);

                foreach ($items as $item) {
                    $orderItem = $this->orderItemRepository->get($result[$item]['order_item_id']);
                    if ($orderItem->getParentItemId()) {
                        $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                        $parentOrderItem->setOrderId($newOrder->getId());
                        $this->orderItemRepository->save($parentOrderItem);
                    }
                    $orderItem->setOrderId($newOrder->getId());
                    $this->orderItemRepository->save($orderItem);
                    $result[$item]['order_id'] = $newOrder->getId();
                }
                $order = $this->changeDataOrder(
                    $result,
                    $items,
                    $newOrder,
                    $this->setShippingAmount($newOrder, $wh, $baseShippingAmount)
                );
                $orders[] = $order;
            } else {
                $order = $this->changeDataOrder(
                    $result,
                    $items,
                    $order,
                    $this->setShippingAmount($order, $wh, $baseShippingAmount)
                );
                $orders[] = $order;
            }
            $numberOrder++;
        }

        return [$result, $orders];
    }

    /**
     * If you do not have enough products in warehouse, we take the other
     *
     * @param \Amasty\MultiInventory\Model\Warehouse\Item\ValidatorResultData[] $result
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return array
     */
    private function checkOrderItem($result, $orderItem)
    {
        $arrayResults = [];
        foreach ($result as $validatorResult) {

            if ($validatorResult->getIsSplitted()) {
                $newItem = $this->createOrderItem($orderItem, $validatorResult->getQty(), $orderItem->getOrder());
                $validatorResult->setOrderItemId($newItem->getItemId());
                $arrayResults[] = $validatorResult->getData();
                continue;
            }
            if ($validatorResult->getIsChanged()) {
                $qty = $validatorResult->getQty();
                if ($orderItem->getParentItemId()) {
                    $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                    $parentOrderItem->setQtyOrdered($qty);
                    $parentOrderItem = $this->changeTotal($parentOrderItem, $qty);
                    $this->orderItemRepository->save($parentOrderItem);
                }
                $orderItem->setQtyOrdered($qty);
                if ($orderItem->getPrice() > 0) {
                    $orderItem = $this->changeTotal($orderItem, $qty);
                }
                $this->orderItemRepository->save($orderItem);
            }
            $arrayResults[] = $validatorResult->getData();
        }

        return $arrayResults;
    }

    /**
     * add Items in Order
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param int $qty
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order\Item
     */
    private function createOrderItem($orderItem, $qty, $order)
    {
        $parent = 0;
        if ($orderItem->getParentItemId()) {
            $newParentOrderItem = $this->orderItemFactory->create();
            $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
            $newParentOrderItem->setData($this->beforeData($parentOrderItem->getData()));
            $newParentOrderItem->setQtyOrdered($qty);
            $newParentOrderItem = $this->changeTotal($newParentOrderItem, $qty);
            $this->orderItemRepository->save($newParentOrderItem);
            $parent = $newParentOrderItem->getId();
            $order->addItem($newParentOrderItem);
        }
        $newOrderItem = $this->orderItemFactory->create();
        $newOrderItem->setData($this->beforeData($orderItem->getData()));
        $newOrderItem->setQtyOrdered($qty);
        if ($parent) {
            $newOrderItem->setParentItemId($parent);
        }
        if ($newOrderItem->getPrice()) {
            $newOrderItem = $this->changeTotal($newOrderItem, $qty);
        }
        $this->orderItemRepository->save($newOrderItem);
        $order->addItem($newOrderItem);
        $this->orderRepository->save($order);

        return $newOrderItem;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param int $warehouse
     * @param float $amount
     * @return float
     */
    private function setShippingAmount($order, $warehouse, $amount)
    {
        if (!empty($this->registry->registry('amasty_quote_methods'))) {
            $shippings = $this->registry->registry('amasty_quote_methods');
            $method = $order->getShippingMethod(true);
            $list = $shippings[$warehouse];
            foreach ($list as $resultMethod) {
                if ($resultMethod['method'] == $method->getMethod()
                    && $resultMethod['carrier_code'] == $method->getCarrierCode()) {
                    $amount = $resultMethod['price'];
                }
            }
        }
        return $amount;
    }

    /**
     * Calc Total for New Order
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $qty
     * @return \Magento\Sales\Model\Order\Item
     */
    private function changeTotal($item, $qty)
    {
        $total = $this->priceCurrency->round($item->getPrice() * $qty);
        $baseTotal = $this->priceCurrency->round($item->getBasePrice() * $qty);
        $totalInclTax = $this->priceCurrency->round($item->getPriceInclTax() * $qty);
        $baseTotalInclTax = $this->priceCurrency->round($item->getBasePriceInclTax() * $qty);
        $item->setRowTotal($total);
        $item->setBaseRowTotal($baseTotal);
        $item->setRowTotalInclTax($totalInclTax);
        $item->setBaseRowTotalInclTax($baseTotalInclTax);
        if ($item->getDiscountPercent()) {
            $discount = $this->priceCurrency->round($total * ($item->getDiscountPercent() / 100));
            $baseDiscount = $this->priceCurrency->round($baseTotal * ($item->getDiscountPercent() / 100));
            $item->setDiscountAmount($discount);
            $item->setBaseDiscountAmount($baseDiscount);
        }

        return $item;
    }

    /**
     * each create order after separate
     *
     * @param array $result
     * @param $items
     * @param \Magento\Sales\Model\Order $order
     * @param float $baseShippingAmount
     * @return \Magento\Sales\Model\Order
     */
    private function changeDataOrder($result, $items, $order, $baseShippingAmount)
    {
        $totalQty = 0;
        $subTotal = 0;
        $baseSubTotal = 0;
        $subTotalInclTax = 0;
        $baseSubTotalInclTax = 0;
        $discount = 0;
        $baseDiscount = 0;
        $tax = 0;
        $baseTax = 0;

        foreach ($items as $item) {
            $orderItem = $this->orderItemRepository->get($result[$item]['order_item_id']);
            if ($orderItem->getParentItemId()) {
                $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                $totalQty += $parentOrderItem->getQtyOrdered();
                $subTotal += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getPrice()
                );
                $baseSubTotal += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getBasePrice()
                );
                $subTotalInclTax += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getPriceInclTax()
                );
                $baseSubTotalInclTax += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getBasePriceInclTax()
                );
                if ($parentOrderItem->getDiscountPercent()) {
                    $discount += $this->priceCurrency->round(
                        $subTotal * ($parentOrderItem->getDiscountPercent() / 100)
                    );
                    $baseDiscount += $this->priceCurrency->round(
                        $baseSubTotal * ($parentOrderItem->getDiscountPercent() / 100)
                    );
                }
                if ($parentOrderItem->getTaxPercent()) {
                    $tax += $this->priceCurrency->round(
                        $subTotal * ($parentOrderItem->getTaxPercent() / 100)
                    );
                    $baseTax += $this->priceCurrency->round(
                        $baseSubTotal * ($parentOrderItem->getTaxPercent() / 100)
                    );
                }
            } else {
                if ($orderItem->getPrice() > 0) {
                    $totalQty += $orderItem->getQtyOrdered();
                    $subTotal += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getPrice()
                    );
                    $baseSubTotal += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getBasePrice()
                    );
                    $subTotalInclTax += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getPriceInclTax()
                    );
                    $baseSubTotalInclTax += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getBasePriceInclTax()
                    );
                    if ($orderItem->getDiscountPercent()) {
                        $discount += $this->priceCurrency->round(
                            $subTotal * ($orderItem->getDiscountPercent() / 100)
                        );
                        $baseDiscount += $this->priceCurrency->round(
                            $baseSubTotal * ($orderItem->getDiscountPercent() / 100)
                        );
                    }
                    if ($orderItem->getTaxPercent()) {
                        $tax += $this->priceCurrency->round(
                            $subTotal * ($orderItem->getTaxPercent() / 100)
                        );
                        $baseTax += $this->priceCurrency->round(
                            $baseSubTotal * ($orderItem->getTaxPercent() / 100)
                        );
                    }
                }
            }
        }
        $amountDiscount = $discount;
        $baseAmountDiscount = $baseDiscount;
        if ($discount > 0) {
            $amountDiscount = -$discount;
            $baseAmountDiscount = -$baseDiscount;
        }
        $shippingAmount = $this->priceCurrency->convert($this->priceCurrency->round($baseShippingAmount));

        $order->setBaseDiscountAmount($baseAmountDiscount);
        $order->setDiscountAmount($amountDiscount);
        $order->setBaseTaxAmount($baseTax);
        $order->setTaxAmount($tax);
        $order->setBaseGrandTotal($baseSubTotal - $baseDiscount + $baseTax + $baseShippingAmount);
        $order->setGrandTotal($subTotal - $discount + $tax + $shippingAmount);
        $order->setBaseSubtotal($baseSubTotal);
        $order->setSubtotal($subTotal);
        $order->setTotalQtyOrdered($totalQty);
        $order->setBaseSubtotalInclTax($baseSubTotalInclTax);
        $order->setSubtotalInclTax($subTotalInclTax);
        $order->setBaseTotalDue($baseSubTotal - $baseDiscount);
        $order->setTotalDue($subTotal - $discount);
        $order->setBaseShippingAmount($baseShippingAmount);
        $order->setBaseShippingInclTax($baseShippingAmount);
        $order->setShippingAmount($shippingAmount);
        $order->setShippingInclTax($shippingAmount);
        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function beforeData($data)
    {
        unset($data['item_id']);
        $data['quote_item_id'] = null;
        $data['parent_item_id'] = null;

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function beforeDataOrder($data)
    {
        $unsetKeys = ['entity_id', 'parent_id', 'item_id', 'order_id'];
        foreach ($unsetKeys as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }

        $unsetKeys = ['increment_id', 'items', 'addresses', 'payment'];
        foreach ($unsetKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
