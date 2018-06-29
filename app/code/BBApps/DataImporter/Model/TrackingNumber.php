<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\Framework\DataObject;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Model\OrderFactory;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Psr\Log\LoggerInterface;

class TrackingNumber extends DataObject
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentLoader
     */
    private $shipmentLoader;

    /**
     * @var ShipmentValidatorInterface
     */
    private $shipmentValidator;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ShipmentSender
     */
    private $shipmentSender;
    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    public function __construct(
        LoggerInterface $logger,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        ShipmentLoader $shipmentLoader,
        ObjectManagerInterface $objectManager,
        ShipmentSender $shipmentSender,
        TransactionFactory $transactionFactory,
        ShipmentValidatorInterface $shipmentValidator,
        array $data = []
    ) {
        parent::__construct($data);

        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->shipmentLoader = $shipmentLoader;
        $this->objectManager = $objectManager;
        $this->shipmentSender = $shipmentSender;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentValidator = $shipmentValidator;
    }

    public function import($data)
    {
        $shipmentLoaderData = $this->prepareData($data);

        if (!$shipmentLoaderData) {
            return false;
        }

        $shipmentData = $shipmentLoaderData['shipment'];
        try {
            $this->shipmentLoader->setData($shipmentLoaderData);
            $shipment = $this->shipmentLoader->load();
            if (!$shipment) {
                return false;
            }

            if (!empty($shipmentData['comment_text'])) {
                $shipment->addComment(
                    $shipmentData['comment_text'],
                    isset($shipmentData['comment_customer_notify']),
                    isset($shipmentData['is_visible_on_front'])
                );

                $shipment->setCustomerNote($shipmentData['comment_text']);
                $shipment->setCustomerNoteNotify(isset($shipmentData['comment_customer_notify']));
            }
            $validationResult = $this->shipmentValidator->validate($shipment, [QuantityValidator::class]);

            if ($validationResult->hasMessages()) {
                $this->logger->log(
                    '100',
                    __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                );
                return false;
            }
            $shipment->register();

            $shipment->getOrder()->setCustomerNoteNotify(!empty($shipmentData['send_email']));

            $this->saveShipment($shipment);

            if (!empty($shipmentData['send_email'])) {
                $this->shipmentSender->send($shipment);
            }
            $this->logger->log('100', 'Import Tracking Number Successful');
            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->log('100', $e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->logger->log('100', $e->getMessage());
            return false;
        }

        return false;
    }

    private function prepareData($data)
    {
        $orderId = null;
        $order = null;
        $shipmentId = null;
        $shipment = [];
        $tracking = [];

        if (!empty($data['order_id'])) {
            $orderId = $data['order_id'];
            $order = $this->orderRepository->get($orderId);
        } elseif (!empty($data['order_increment_id'])) {
            $order = $this->orderFactory->create()->loadByIncrementId($data['order_increment_id']);
            $orderId = $order->getId();
        }

        if (!$orderId) {
            $this->logger->log('100', __('Order Id Not Found'));
            return false;
        }

        // #todo: apply shipmentId logic
        // end

        $items = [];
        foreach ($order->getAllItems() as $orderItem) {
            // Check if order item has qty to ship or is virtual
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $items[$orderItem->getId()] = $orderItem->getQtyToShip();
        }

        if (empty($items)) {
            $this->logger->log('100', __('Order has no item'));
            return false;
        }
        $shipment['items'] = $items;
        if (!empty($data['comment'])) {
            $shipment['comment_text'] = $data['comment'];
            $shipment['comment_customer_notify'] = '1';
        }

        if (!empty($data['notify_customer']) && $data['notify_customer'] == '1') {
            $shipment['send_email'] = '1';
        }

        if (!empty($data['tracking_number'])) {
            $carrierCode = 'custom';
            $title = __('Custom Value');

            //#todo: add carrier code following by shipment method
            $tracking[] = [
                'carrier_code' => $carrierCode,
                'title' => $title,
                'number' => $data['tracking_number']
            ];
        }

        return [
            'order_id' => $orderId,
            'shipment_id' => $shipmentId,
            'shipment' => $shipment,
            'tracking' => $tracking
        ];
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Shipment $shipment
     * @return $this
     */
    private function saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->transactionFactory->create();
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
}
