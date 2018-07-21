<?php

namespace Magesales\AddressValidation\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Form extends Template
{
    protected $helper;

    protected $response;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magesales\AddressValidation\Helper\Data $helper,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function getValidateUrl()
    {
        return $this->_urlBuilder->getUrl('address_validation/ajax/validation');
    }

    public function _toHtml()
    {
        if ($this->getHelper()->getEnable()){
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
