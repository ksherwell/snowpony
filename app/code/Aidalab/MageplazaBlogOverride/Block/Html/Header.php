<?php

namespace Aidalab\MageplazaBlogOverride\Block\Html;

use Infortis\Base\Helper\Data as HelperData;
use Infortis\Base\Helper\Template\Theme\Html\Header as HelperTemplateHtmlHeader;
use Infortis\Infortis\Helper\Connector\Infortis\UltraMegamenu as HelperConnectorUltraMegamenu;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Helper\Data;

class Header extends \Infortis\Base\Block\Html\Header
{
    /**
     * @var Data
     */
    private $helperMageplaza;

    private $storeManager;

    public function __construct(
        Context $context,
        HelperData $helperData,
        HelperTemplateHtmlHeader $helperTemplateHtmlHeader,
        HelperConnectorUltraMegamenu $helperConnectorUltraMegamenu,
        Data $helperMageplaza,
        StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->helperMageplaza = $helperMageplaza;
        $this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $helperData,
            $helperTemplateHtmlHeader,
            $helperConnectorUltraMegamenu,
            $data
        );
    }

    public function getBlogUrl()
    {
        return $this->helperMageplaza->isEnabled() ? $this->helperMageplaza->getBlogUrl() : '';
    }

    public function getBlogName()
    {
        $store = null;

        try {
            $store = $this->storeManager->getStore()->getId();
        }catch (\Exception $e){
        }

        return $this->helperMageplaza->isEnabled() ? $this->helperMageplaza->getBlogName($store) : '';
    }
}
