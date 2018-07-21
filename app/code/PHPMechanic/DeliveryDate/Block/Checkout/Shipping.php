<?php

namespace PHPMechanic\DeliveryDate\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\ScopeInterface;

/**
 * Mustishipping checkout shipping
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Shipping extends \Magento\Multishipping\Block\Checkout\Shipping
{


    protected $scopeConfig;
    protected $_filterGridFactory;
    protected $_taxHelper;
    protected $priceCurrency;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->priceCurrency = $priceCurrency;
        $this->_taxHelper = $taxHelper;
        $this->_filterGridFactory = $filterGridFactory;
        $this->_multishipping = $multishipping;
        \Magento\Sales\Block\Items\AbstractItems::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    public function getDisabled()
    {
        return $this->scopeConfig->getValue('phpmechanic_deliverydate/general/disabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAdditionalDisabled() {
        return $this->scopeConfig->getValue('phpmechanic_deliverydate/general/additional', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDateFormat()
    {
        return $this->scopeConfig->getValue('phpmechanic_deliverydate/general/format', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Shipping Methods') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        $this->setTemplate('PHPMechanic_DeliveryDate::checkout/shipping.phtml');
        return parent::_prepareLayout();
    }
}
