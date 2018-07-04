<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Model\Order\Address\Renderer;

class EmailNotification
{
    const XML_PATH_EMAIL_NEW_ORDER = 'amasty_multi_inventory/emails/order/template';

    const XML_PATH_EMAIL_LOW_STOCK = 'amasty_multi_inventory/emails/low_stock/template';

    const XML_SENDER = 'amasty_multi_inventory/emails/sender';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * @var Warehouse\ItemFactory
     */
    private $factory;

    /**
     * @var Warehouse\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $stockRepository;

    /**
     * EmailNotification constructor.
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param Warehouse\ItemFactory $factory
     * @param Warehouse\Order\ItemFactory $orderItemFactory
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory $orderItemFactory,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $stockRepository
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->orderItemFactory = $orderItemFactory;
        $this->urlBuilder = $urlBuilder;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @param $template
     * @param $sender
     * @param array $templateParams
     * @param null $storeId
     * @param null $email
     */
    private function sendEmailTemplate(
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
        if ($email) {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($this->scopeConfig->getValue($sender, 'store', $storeId));
            if (strpos($email, ",") === false) {
                $transport->addTo($email);
            } else {
                $emails = explode(",", $email);
                $counter = 1;
                foreach ($emails as $record) {
                    if ($counter == 1) {
                        $transport->addTo($record);
                    } else {
                        $transport->addCc($record);
                    }
                    $counter++;
                }
            }
            $mailTransport = $transport->getTransport();

            $mailTransport->sendMessage();
        }
    }

    /**
     * @param $productId
     * @param $warehouse
     */
    public function sendLowStock($productId, $warehouse)
    {
        $item = $this->stockRepository->getByProductWarehouse($productId, $warehouse);
        if (count($item->getWarehouse()->getLowStockNotification())) {
            $this->sendEmailTemplate(
                self::XML_PATH_EMAIL_LOW_STOCK,
                self::XML_SENDER,
                [
                    'item' => $item,
                    'qty' => (int)$item->getQty(),
                    'available_qty' => (int)$item->getAvailableQty(),
                    'ship_qty' => (int)$item->getShipQty(),
                    'product' => $item->getProduct(),
                    'warehouse' => $item->getWarehouse()
                ],
                $this->storeManager->getStore()->getId(),
                $item->getWarehouse()->getLowStockNotification()
            );
        }
    }

    /**
     * @param $order
     */
    public function setNewOrder($order)
    {
        $collection = $this->orderItemFactory->create()->getCollection()->getWarehousesFromOrder($order->getId());
        $emails = [];
        foreach ($collection as $item) {
            if ($item->getWarehouse()->getOrderEmailNotification()) {
                $emails[] = $item->getWarehouse()->getOrderEmailNotification();
            }
        }

        if (count($emails)) {
            if (count($emails) > 1) {
                $emails = implode(',', $emails);
            } else {
                $emails = $emails[0];
            }
            $this->sendEmailTemplate(
                self::XML_PATH_EMAIL_NEW_ORDER,
                self::XML_SENDER,
                [
                    'store' => $this->storeManager->getStore(),
                    'order' => $order,
                    'url' => $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
                ],
                $this->storeManager->getStore()->getId(),
                $emails
            );
        }
    }
}
