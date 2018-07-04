<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator
     */
    protected $quantityValidator;

    public function __construct(
        \Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator $quantityValidator
    ) {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->quantityValidator->validate($observer);
    }
}
