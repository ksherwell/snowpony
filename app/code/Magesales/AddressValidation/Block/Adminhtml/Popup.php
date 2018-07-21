<?php

namespace Magesales\AddressValidation\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Popup extends Template
{
    /**
     * @var \Magesales\AddressValidation\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magesales\AddressValidation\Model\Google\Validation|\Magesales\AddressValidation\Model\Ups\Validation|\Magesales\AddressValidation\Model\Usps\Validation
     */
    protected $addressValidator;

    /**
     * @param Template\Context $context
     * @param \Magesales\AddressValidation\Helper\Data $helper
     * @param \Magesales\AddressValidation\Model\Validation\Validator $addressValidator
     * @param array $data
     * @throws \Exception
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magesales\AddressValidation\Helper\Data $helper,
        \Magesales\AddressValidation\Model\Validation\Validator $addressValidator,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->addressValidator = $addressValidator->getValidator();

        parent::__construct($context, $data);
    }

    /**
     * @return \Magesales\AddressValidation\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function getValidateUrl()
    {
        return $this->_urlBuilder->getUrl('address_validation/ajax/validation');
    }

    /**
     * @return mixed
     */
    public function getAllowNotValidAddress()
    {
        return $this->getHelper()->getAllowNotValidAddress();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isEnabled()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->getHelper()->getEnable() && $this->addressValidator->getEnable();
    }
}
