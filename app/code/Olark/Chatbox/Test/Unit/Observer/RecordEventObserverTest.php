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
namespace Olark\Chatbox\Test\Unit\Observer;

class RecordEventObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Olark\Chatbox\Observer\RecordEventObserver
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $coreSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    public function setUp()
    {
        $this->coreSessionMock = $this->getMock(
            'Magento\Framework\Session\Generic',
            ['setData', 'getData'],
            [],
            '',
            false
        );
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->dateTimeMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
        $this->model = new \Olark\Chatbox\Observer\RecordEventObserver(
            $this->coreSessionMock,
            $this->dateTimeMock
        );
    }

    public function testExecute()
    {
        /** @var Event|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects($this->once())
            ->method('getName')
            ->willReturn('test_event');

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $this->coreSessionMock
            ->expects($this->once())
            ->method('getData')
            ->with('olark_chatbox_events')
            ->willReturn(null);

        $this->coreSessionMock
            ->expects($this->once())
            ->method('setData')
            ->with('olark_chatbox_events', [[
                'type' => 'test_event',
                'timestamp' => $this->dateTimeMock->gmtTimestamp()
            ]]);

        $this->model->execute($this->observerMock);
    }
}
