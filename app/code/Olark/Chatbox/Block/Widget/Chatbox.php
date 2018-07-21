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
namespace Olark\Chatbox\Block\Widget;

/**
 * Produces Olark Chatbox html
 *
 * @return string
 */
class Chatbox extends \Olark\Chatbox\Block\AbstractBlock implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Olark_Chatbox::chatbox.phtml');
    }

    public function getSiteId()
    {
        return $this->getData('site_id');
    }

    public function getCustomConfig()
    {
        return $this->getData('custom_config');
    }
}
