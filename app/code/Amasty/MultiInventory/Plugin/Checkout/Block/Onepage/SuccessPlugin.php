<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Checkout\Block\Onepage;

class SuccessPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    private static $ORDERS;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\Order\Config $orderConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->objectFactory   = $objectFactory;
        $this->orderFactory = $orderFactory;
        $this->httpContext = $httpContext;
        $this->orderConfig = $orderConfig;
    }

    public function beforeToHtml(\Magento\Checkout\Block\Onepage\Success $subject)
    {
        $orders = $this->getSeparateOrderIds();
        if (is_array($orders) && count($orders) > 1 && !$subject->hasData('multiple_orders')) {
            $data = [];
            foreach ($orders as $orderId) {
                $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                $data[] = $this->objectFactory->create()
                    ->addData([
                        'is_order_visible' => $this->isVisible($order),
                        'view_order_url' => $subject->getUrl(
                            'sales/order/view/',
                            ['order_id' => $order->getEntityId()]
                        ),
                        'print_url' => $subject->getUrl(
                            'sales/order/print',
                            ['order_id' => $order->getEntityId()]
                        ),
                        'can_print_order' => $this->isVisible($order),
                        'can_view_order'  => $this->canViewOrder($order),
                        'order_id'  => $order->getIncrementId()
                    ]);
            }
            $subject->setData('is_multiple_orders', true);
            $subject->setData('multiple_orders', $data);
        }
    }

    private function getSeparateOrderIds()
    {
        if (self::$ORDERS === null) {
            self::$ORDERS = $this->checkoutSession->getSeparetedOrderIds(true);
        }
        return self::$ORDERS;
    }

    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible($order)
    {
        return !in_array(
            $order->getStatus(),
            $this->orderConfig->getInvisibleOnFrontStatuses()
        );
    }

    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder($order)
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
        && $this->isVisible($order);
    }
}
