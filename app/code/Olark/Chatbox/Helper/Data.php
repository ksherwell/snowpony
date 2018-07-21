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
namespace Olark\Chatbox\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * Module config settings
     */
    const XML_PATH_SETTINGS_SITE_ID = 'olark_chatbox/settings/site_id';
    const XML_PATH_SETTINGS_CUSTOM_CONFIG = 'olark_chatbox/settings/custom_config';

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * Returns the version of this Olark CartSaver plugin.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $module = $this->moduleList->getOne('Olark_Chatbox');

        return isset($module['setup_version']) ? $module['setup_version'] : '';
    }

    /**
     * Returns Site ID
     *
     * @param null $store
     * @param $scopeType
     * @return mixed
     */
    public function getSiteId($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SETTINGS_SITE_ID,
            $scopeType,
            $store
        );
    }

    /**
     * Returns Custom Config
     *
     * @param null $store
     * @param $scopeType
     * @return mixed
     */
    public function getCustomConfig($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SETTINGS_CUSTOM_CONFIG,
            $scopeType,
            $store
        );
    }

    /**
     * Returns Site Id field Description
     *
     * @return string
     */
    public function getSiteIdDescription()
    {
        $moduleVersion = $this->getModuleVersion();
        // @codingStandardsIgnoreStart
        return <<<COMMENT
            <p class="note" style="font-weight:bold;margin-top: 5px;margin-bottom:10px;font-size:1.5em">Get started in 5 minutes.</p>
            <ol style="list-style: decimal; margin-left: 30px; font-size: 1.5em; line-height: 1.5em;">
                <li>
                    <a target="_blank" href="https://olark.com/signup/create_new_account?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">
                    Create an Olark account</a>
                    if you don't have one.
                </li>
                <li>
                    <a target="_blank" href="https://olark.com/settings/site-id?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">
                    Find your Site ID</a>
                    and type it above.
                </li>
                <li>
                    <a target="_blank" href="https://olark.com/extensions/cartsaver?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">
                    Configure CartSaver notifications</a>
                    in your Olark account.
                </li>
                <li>
                    <a target="_blank" href="https://chat.olark.com/?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">
                    Start chatting with your customers.</a>
                    That's it!
                </li>
            </ol>

            <p style="margin-top: 1em">
                <strong>Need help?</strong> You can
                <a target="_blank" href="http://olark.com/help/magento-connect?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">
                read our tutorial</a>,
                or contact us by
                <a target="_blank" href="http://olark.com/help/magento-connect?cartsaver=magento&amp;version={$moduleVersion}&amp;intent=chat&amp;rid=_integration_magento2_config">
                live chat</a>
                or
                <a target="_blank" href="mailto:support+magento@olark.com">
                email</a>.
            </p>

            <hr style="border: none; border: solid #ccc 1px; margin: 3em 0 3em 0;"/>

            <h3>
                Save the sale with Olark CartSaver for Magento!
            </h3>

            <p style="margin-top: 1em">
                Olark lets you answer questions immediately,
                keep customers browsing your store and save the sale!
            </p>

            <p style="margin-top: 1em"><strong>See what customers do on your Magento store.</strong> You'll know
              the second customers want to buy when Olark
              CartSaver notifies you in real-time whenever customers
              update their cart, proceed to checkout, and complete the sale.
              Then reach out and help. You'll keep your customers interested
              all the way to purchase.
            </p>

            <p style="margin-top: 1em">
                <strong><a target="_blank" href="http://olark.com/redirect/magento-connect?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">

                Learn more and give us a good review in Magento Connect</a></strong>
            </p>

            <hr style="border: none; border: solid #ccc 1px; margin: 3em 0 3em 0;"/>

            <h3>
                Advanced: Developer API snippets
            </h3>

            <p style="margin-top: 1em">
                Olark has an extensive Javascript API which developers
                can use to change the behavior of how Olark works.
                <a target="_blank" href="http://olark.com/documentation?cartsaver=magento&amp;version={$moduleVersion}&amp;rid=_integration_magento2_config">
                Read our documentation for more information</a>.</p>

            <p style="margin-top: 1em">To include your own HTML or Javascript, just paste it below. Please <a target="_blank" href="mailto:support+magento@olark.com">get in touch</a> if you run into any problems.</p>

            <p style="margin-top: 1em">
                <strong>
                    Don't forget to wrap your
                    Javascript in &lt;script&gt;&lt;/script&gt; tags!
                </strong>
            </p>
COMMENT;
        // @codingStandardsIgnoreEnd
    }
}
