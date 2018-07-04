<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Quote\Observer\Webapi;

use Magento\Framework\Registry;

class SubmitObserver
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * SubmitObserver constructor.
     * @param \Amasty\MultiInventory\Helper\System $system
     */
    public function __construct(
        \Amasty\MultiInventory\Helper\System $system,
        Registry $registry
    ) {
        $this->system = $system;
        $this->registry = $registry;
    }

    /**
     * avoid send email for Core. Email will be sent by Multiinventory.
     * Becouse order can be splitted
     *
     * @param \Magento\Quote\Observer\Webapi\SubmitObserver $submitObserver
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function beforeExecute(
        \Magento\Quote\Observer\Webapi\SubmitObserver $submitObserver,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($this->system->isMultiEnabled()) {
            $this->registry->unregister('multiinventory_cant_send_new_email');
            if (!$order->getCanSendNewEmailFlag()) {
                $this->registry->register('multiinventory_cant_send_new_email', true);
            }

            // avoid send email for Core. Email will be sent by Multiinventory. Becouse order can be splitted
            $order->setCanSendNewEmailFlag(false);
        }
    }
}
