<?php
/**
 * Widget that adds Olark Live Chat to Magento stores.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@olark.com so we can send you a copy immediately.
 *
 * @category    Olark
 * @package     Olark_Chatbox
 * @copyright   Copyright 2012. Habla, Inc. (http://www.olark.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Olark\Chatbox\Test\Unit\Block;

class ChatboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Olark\Chatbox\Block\Widget\Chatbox
     */
    private $chatboxElement;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $pricingHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleListMock;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false, false);
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false, false);
        $this->pricingHelperMock = $this->getMock('Magento\Framework\Pricing\Helper\Data', [], [], '', false, false);
        $this->helperMock = $this->getMock('Olark\Chatbox\Helper\Data', [], [], '', false, false);
        $this->moduleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false, false);

        $this->sessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['getData'],
            [],
            '',
            false
        );
        $this->contextMock = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            ['getSession'],
            [],
            '',
            false
        );

        $this->contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);

        $this->chatboxElement = $this->objectManager->getObject(
            'Olark\Chatbox\Block\Chatbox',
            [
                'context' => $this->contextMock,
                'customerSession' => $this->customerSessionMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'pricingHelper' => $this->pricingHelperMock,
                'helper' => $this->helperMock,
                'moduleList' => $this->moduleListMock,
            ]
        );
    }

    public function tearDown()
    {
        $this->chatboxElement = null;
    }

    public function testGetSiteId()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('getSiteId')
            ->willReturn('test');

        $this->assertEquals('test', $this->chatboxElement->getSiteId());
    }

    public function testGetCustomConfig()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('getCustomConfig')
            ->willReturn('test');

        $this->assertEquals('test', $this->chatboxElement->getCustomConfig());
    }

    public function testGetCustomerJsonNotLoggedIn()
    {
        $this->customerSessionMock
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn(null);
        $json = $this->chatboxElement->getCustomerDataJson();
        $this->assertEquals(\Zend_Json::encode([]), $json);
    }

    public function testGetCustomerJsonLoggedIn()
    {
        $expects = [
            'name' => 'test name',
            'email' => null,
            'billing_address' => '',
            'shipping_address' => '',
        ];

        $customerMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn($expects['name']);

        $this->customerSessionMock
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $json = $this->chatboxElement->getCustomerDataJson();
        $this->assertEquals(\Zend_Json::encode($expects), $json);
    }

    public function testGetProductsJsonEmptyCart()
    {
        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getAllVisibleItems'],
            [],
            '',
            false
        );

        $quoteMock
            ->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([]);

        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $json = $this->chatboxElement->getProductsDataJson();
        $this->assertEquals(\Zend_Json::encode([]), $json);
    }

    public function testGetProductsJsonWithCart()
    {
        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getAllVisibleItems'],
            [],
            '',
            false
        );

        $quoteItemMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item',
            ['getData', 'getName', 'getQty', 'getSku', 'getPrice'],
            [],
            '',
            false
        );
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([]);
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('test name');
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1.0);
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn('TESTSKU');
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn(10);

        $quoteMock
            ->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$quoteItemMock]);

        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $expects = [[
            'name' => 'test name',
            'sku' => 'TESTSKU',
            'quantity' => 1,
            'price' => 10,
            'magento' => [
                'formatted_price' => null,
            ],
        ]];

        $json = $this->chatboxElement->getProductsDataJson();
        $this->assertEquals(\Zend_Json::encode($expects), $json);
    }

    public function testGetMagentoDataJsonEmptyCart()
    {
        $this->sessionMock
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([]);

        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getAllVisibleItems', 'getTotals'],
            [],
            '',
            false
        );

        $quoteMock
            ->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([]);
        $quoteMock
            ->expects($this->atLeastOnce())
            ->method('getTotals')
            ->willReturn([]);

        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $expects = [
            'total' => 0,
            'formatted_total' => null,
            'extra_items' => [],
            'recent_events' => [],
        ];

        $json = $this->chatboxElement->getMagentoDataJson();
        $this->assertEquals(\Zend_Json::encode($expects), $json);
    }

    public function testGetMagentoDataJsonWithCart()
    {
        $this->sessionMock
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([]);

        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getAllVisibleItems', 'getTotals'],
            [],
            '',
            false
        );

        $quoteItemMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item',
            ['getData', 'getName', 'getQty', 'getSku', 'getPrice'],
            [],
            '',
            false
        );
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1.0);
        $quoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn(10);

        $quoteMock
            ->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$quoteItemMock]);

        $subtotalValue = 200;
        $subtotalMock = $this->getMock(
            '\Magento\Framework\DataObject',
            ['getValue', 'getCode'],
            [],
            '',
            false
        );
        $subtotalMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($subtotalValue);
        $subtotalMock
            ->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('subtotal');
        $totals = ['subtotal' => $subtotalMock];
        $quoteMock
            ->expects($this->atLeastOnce())
            ->method('getTotals')
            ->willReturn($totals);

        $this->checkoutSessionMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $expects = [
            'total' => 200,
            'formatted_total' => null,
            'extra_items' => [[
                'name' => 'subtotal',
                'price' => $subtotalValue,
                'formatted_price' => null,
            ]],
            'recent_events' => [],
        ];

        $json = $this->chatboxElement->getMagentoDataJson();
        $this->assertEquals(\Zend_Json::encode($expects), $json);
    }
}
