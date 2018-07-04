<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Script;

class Google extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $helper;

    /**
     * Google constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Amasty\MultiInventory\Helper\System $helper
     * @param array|null $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\MultiInventory\Helper\System $helper,
        array $data = null
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function isAddressSuggestionEnabled()
    {
        return $this->helper->isAddressSuggestionEnabled();
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->helper->getGoogleMapsKey();
    }
}
