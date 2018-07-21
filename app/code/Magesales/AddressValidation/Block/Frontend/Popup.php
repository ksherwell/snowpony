<?php

namespace Magesales\AddressValidation\Block\Frontend;

use Magento\Framework\View\Element\Template;

class Popup extends \Magento\Framework\View\Element\Template
{
    protected $helper;

    protected $addressValidator;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magesales\AddressValidation\Helper\Data $helper,
        \Magesales\AddressValidation\Model\Validation\Validator $addressValidator,
        array $data
    ) {
        $this->helper = $helper;
        $this->addressValidator = $addressValidator->getValidator();

        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function getValidateUrl()
    {
        return $this->_urlBuilder->getUrl('address_validation/ajax/validation');
    }

    public function getAllowNotValidAddress()
    {
        return $this->getHelper()->getAllowNotValidAddress();
    }

    public function _toHtml()
    {
        if ($this->isEnabled()){
            return parent::_toHtml();
        }
        return '';
    }

    protected function isEnabled()
    {
        return $this->getHelper()->getEnable() && $this->addressValidator->getEnable();
    }
}
