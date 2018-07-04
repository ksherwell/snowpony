<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote\ItemFactory;

class Cart extends AbstractHelper
{

    const CONFIGURABLE = 'configurable';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * Cart constructor.
     * @param Context $context
     * @param ItemFactory $itemFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->itemFactory = $itemFactory;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param $data
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function changeRequestAddress(\Magento\Quote\Model\Quote\Address\RateRequest $request, $data)
    {
        $request->setCountryId(
            $data['country']
        )->setRegionId(
            $data['state']
        )->setCity(
            $data['city']
        )->setPostcode(
            $data['zip']
        )->setOrigCountryId(
            $data['country']
        )->setOrigRegionId(
            $data['state']
        )->setOrigCountry(
            $data['country']
        )->setOrigRegionCode(
            $data['state']
        )->setOrigCity(
            $data['city']
        )->setOrigPostcode(
            $data['zip']
        );

        return $request;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Magento\Quote\Model\Quote\Item[]              $items
     * @param \Magento\Quote\Model\Quote                     $quote
     *
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function changeRequestItems(\Magento\Quote\Model\Quote\Address\RateRequest $request, $items, $quote)
    {
        $data = $this->calcData($items, $quote);
        $request->setAllItems($items);
        $request->setPackageValue($data['base_row_total']);
        $request->setPackageValueWithDiscount($data['base_discount_amount']);
        $request->setPackageQty($data['qty']);
        $request->setPackagePhysicalValue($data['base_row_total']);
        $request->setBaseSubtotalInclTax($data['base_subtotal_incl_tax']);
        $weight = 0;

        if ($data['weight']) {
            $weight = $data['weight'] * $data['qty'];
        }
        $request->setFreeMethodWeight($weight);
        $request->setPackageWeight($data['package_weight']);

        return $request;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @param \Magento\Quote\Model\Quote       $quote
     *
     * @return array
     */
    private function calcData($items, $quote)
    {
        $data = [
            'base_row_total' => 0,
            'base_discount_amount' => 0,
            'qty' => 0,
            'base_subtotal_incl_tax' => 0,
            'weight' => 0,
            'package_weight' => 0
        ];
        foreach ($items as $item) {
            $element = $item;
            if ($item->getParentItemId()) {
                $element = $quote->getItemById($item->getParentItemId());
            }

            $element->calcRowTotal();
            $data['base_row_total'] += $element->getBaseRowTotal();
            $data['base_discount_amount'] += ($element->getBaseRowTotal()
                - ($element->getBaseRowTotal() * $element->getDiscountPercent()));
            $data['qty'] += $element->getQty();
            $data['base_subtotal_incl_tax'] += $element->getBasePriceInclTax() * $element->getQty();
            if ($element->getWeight()) {
                $data['weight'] += $element->getWeight();
            }
            $data['package_weight'] = $element->getRowWeight() ?: $element->getWeight();
        }

        return $data;
    }

    /**
     * Sum shipping rates if sevaral warehouses
     *
     * @param $sumResults
     * @return array
     */
    public function sumShipping($sumResults)
    {
        $result = [];
        if (count($sumResults) > 1) {
            foreach ($sumResults as $res) {
                if (empty($result)) {
                    $result = $res;
                    continue;
                }
                foreach ($res->getAllRates() as $method) {
                    foreach ($result->getAllRates() as $resultMethod) {
                        if ($method->getMethod() == $resultMethod->getMethod()) {
                            $resultMethod->setPrice($method->getPrice() + $resultMethod->getPrice());
                            continue;
                        }
                    }
                }
            }
        } else {
            reset($sumResults);
            $result = current($sumResults);
        }

        return $result;
    }

    /**
     * @param $shipment
     * @param \Amasty\MultiInventory\Model\Warehouse $warehouse
     * @return mixed
     */
    public function changePrice($shipment, \Amasty\MultiInventory\Model\Warehouse $warehouse)
    {
        if ($shippings = $warehouse->getShippings()) {
            foreach ($shippings as $item) {
                foreach ($shipment->getAllRates() as $method) {
                    $isMethod = 0;
                    if ($item->getShippingMethod() == $method->getMethod()) {
                        $method->setPrice($item->getRate());
                        $isMethod = 1;
                    }
                    if (!$isMethod && $item->getShippingMethod() == $method->getCarrier()) {
                        $method->setPrice($item->getRate());
                    }
                }
            }
        }

        return $shipment;
    }
}
