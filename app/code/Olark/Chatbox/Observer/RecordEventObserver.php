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
namespace Olark\Chatbox\Observer;

use Magento\Framework\Event\ObserverInterface;

class RecordEventObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $coreSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Framework\Session\Generic $coreSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\Session\Generic $coreSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->coreSession = $coreSession;
        $this->dateTime = $dateTime;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $events = [];

        // Get a list of the events
        $olarkChatboxEvents = $this->coreSession->getData('olark_chatbox_events');
        if (is_array($olarkChatboxEvents)) {
            $events = $olarkChatboxEvents;
        }

        // Append to this list. Add a timestamp so we know the
        // relative times that the events happened.
        $events[] = [
            'type' => $observer->getEvent()->getName(),
            'timestamp' => $this->dateTime->gmtTimestamp(),
        ];

        $this->coreSession->setData('olark_chatbox_events', $events);

        return $this;
    }
}
