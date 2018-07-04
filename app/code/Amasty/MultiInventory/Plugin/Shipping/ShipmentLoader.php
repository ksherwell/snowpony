<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Shipping;

use Magento\Framework\DataObject;

class ShipmentLoader
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    public function __construct(\Amasty\MultiInventory\Helper\System $system)
    {
        $this->system = $system;
    }

    /**
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $subject
     * @param bool|\Magento\Sales\Model\Order\Shipment                    $result
     *
     * @return bool|\Magento\Sales\Model\Order\Shipment
     */
    public function afterLoad(
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $subject,
        $result
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $result;
        }
        if ($result instanceof DataObject) {
            $shipmentData = $subject->getShipment();
            if (isset($shipmentData['warehouse'])) {
                $result->setData('warehouse', $shipmentData['warehouse']);
            }
        }

        return $result;
    }
}
