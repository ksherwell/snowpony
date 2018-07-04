<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseShippingInterface extends WarehouseAbstractInterface
{
    const SHIPPING_METHOD = 'shipping_method';

    const RATE = 'rate';


    /**
     * @return string
     */
    public function getShippingMethod();

    /**
     * @return double|null
     */
    public function getRate();

    /**
     * @param $method
     * @return WarehouseShippingInterface
     */
    public function setShippingMethod($method);

    /**
     * @param $rate
     * @return WarehouseShippingInterface
     */
    public function setRate($rate);
}
