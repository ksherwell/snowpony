<?php

namespace Magesales\AddressValidation\Block\Adminhtml\System\Config;

use Magesales\AddressValidation\Helper\Data;
use Magesales\AddressValidation\Model\Validation\Validator;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;

class Validation extends Field
{
    protected $helper;
    protected $validator;
    protected $message;
    protected $color;

    public function __construct(
        Context $context,
        Data $helper,
        Validator $validator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->validator = $validator->getValidator();
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->validateApiCredentials();
        return "<span style='margin-bottom:-8px; display:block; color:{$this->color}'>{$this->message}</span><style>#row_magesales_opc_addresvalidation_opc_api_settings_validtion label>span{display:none}</style>";
    }

    protected function validateApiCredentials()
    {
        if($this->helper->getEnable() == 0)
        {
            $this->color = '#D40707';
            $this->message = __("Extension is disabled");
            return;
        }

        if($this->validateApi() == false)
        {
            $this->color = '#D40707';
            $this->message = __("Incorrect API credentials");
            return;
        }

        $this->color = '#059147';
        $this->message = __("Validation settings are correct");
    }

    protected function validateApi()
    {
        $request = [
            "street" => "251 Florida St",
            "city" => "BATON ROUGE",
            "country_id" => "US",
            "region_id" => "28",
            "postcode" => "70801"
        ];

        $this->validator->setAddressForValidation($request);
        $this->validator->validate();
        $response = $this->validator->getValidationResponse()->toDataObject();

        $suggested = $response->getSuggestedAddresses();

        if($response->getError() == true || count($suggested) == 0){
            return false;
        }

        return true;
    }
}
