<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Shipping\Items;

use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

class AfterRenderer extends \Magento\Backend\Block\Template
{

    /**
     * @var Warehouse\Order\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Warehouse\ItemFactory
     */
    private $itemWh;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * AfterRenderer constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Warehouse\Order\ItemFactory $itemFactory
     * @param Warehouse\ItemFactory $itemWh
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory $itemFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $itemWh,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->itemFactory = $itemFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->itemWh = $itemWh;
        $this->shipmentRepository = $shipmentRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
    }


    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->getData('options');
    }

    /**
     * add field for change items in order shipment, invoice and creditmemo
     *
     * @return string
     */
    public function jsonData()
    {
        $data = [];
        $order_id = $this->getRequest()->getParam('order_id');
        if (!$order_id) {
            if ($this->getRequest()->getParam('shipment_id')) {
                $shipment_id = $this->getRequest()->getParam('shipment_id');
                $shipment = $this->shipmentRepository->get($shipment_id);
                $order_id = $shipment->getOrderId();
            }
            if ($this->getRequest()->getParam('invoice_id')) {
                $invoice_id = $this->getRequest()->getParam('invoice_id');
                $invoice = $this->invoiceRepository->get($invoice_id);
                $order_id = $invoice->getOrderId();
            }
            if ($this->getRequest()->getParam('creditmemo_id')) {
                $creditmemo_id = $this->getRequest()->getParam('creditmemo_id');
                $creditmemo = $this->creditmemoRepository->get($creditmemo_id);
                $order_id = $creditmemo->getOrderId();
            }
        }

        if ($order_id) {
            $collection = $this->itemFactory->create()->getCollection()->getDataOrder($order_id);
            foreach ($collection as $item) {
                $fields = $item->toArray();
                $idField = $fields['parent'];
                if (!$idField) {
                    $idField = $fields['item'];
                }
                $data[$idField] = [
                    'data' => $fields,
                    'list' => $this->itemWh->create()->getItems($fields['product'])
                ];
            }
        }

        return $this->jsonEncoder->encode($data);
    }
}
