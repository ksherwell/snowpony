<?php

namespace Magesales\AddressValidation\Block\Frontend;

use Magento\Framework\View\Element\Template;

class Form extends Template
{
    protected $_helper;

    protected $response;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magesales\AddressValidation\Helper\Data $helper,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->_helper = $helper;
    }

    public function getHelper()
    {
        return $this->_helper;
    }

    public function getValidateUrl()
    {
        return $this->_urlBuilder->getUrl('address_validation/ajax/validation');
    }

    public function _toHtml()
    {
        if ($this->_helper->getEnable()){
            return parent::_toHtml();
        }
        return '';
    }

    public function setValidationResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getValidationResponse()
    {
        return $this->response;
    }
}
