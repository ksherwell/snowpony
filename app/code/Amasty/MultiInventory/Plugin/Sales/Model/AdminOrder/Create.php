<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Sales\Model\AdminOrder;

class Create
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * SubmitObserver constructor.
     * @param \Amasty\MultiInventory\Helper\System $system
     */
    public function __construct(
        \Amasty\MultiInventory\Helper\System $system
    ) {
        $this->system = $system;
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $order
     */
    public function beforeCreateOrder(
        \Magento\Sales\Model\AdminOrder\Create $order
    ) {
        if ($this->system->isMultiEnabled() && $this->system->getDefinationWarehouse()) {
            $order->setSendConfirmation(false);
        }
    }
}
