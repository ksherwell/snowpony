<?php

namespace Magesales\AddressValidation\Model\Validation;

use Magento\Framework\DataObject;

class Response extends DataObject
{
    protected $isError;
    protected $errorMessage;
    protected $isValid;

    /* @var \Magesales\AddressValidation\Model\Validation\Address */
    protected $originalAddress;

    /* @var \Magesales\AddressValidation\Model\Validation\Address[] */
    protected $suggestedAddresses;

    public function __construct(
        Address $address,
        array $data = []
    ) {
        parent::__construct($data);
        $this->originalAddress = $address;
        $this->initParams();
    }


    protected function initParams()
    {
        $this->isError = false;
        $this->isValid = false;
        $this->errorMessage = '';
        $this->suggestedAddresses = array();
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getIsError()
    {
        return $this->isError;
    }

    public function getIsValid()
    {
        return $this->isValid;
    }

    public function getOriginalAddress()
    {
        return $this->originalAddress;
    }

    public function getSuggestedAddresses()
    {
        return $this->suggestedAddresses;
    }

    public function setOriginAddress(Address $address)
    {
        $address->updateRegionData();
        $this->originalAddress = $address;
        return $this;
    }

    public function addSuggestedAddress(Address $address)
    {
        $address->updateRegionData();
        $this->suggestedAddresses[] = $address;
        return $this;
    }

    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function addError($message)
    {
        $this->isError = true;
        $this->errorMessage = $message;
        return $this;
    }

    public function toArray(array $keys = [])
    {
        return
        [
            'error' => $this->isError,
            'error_message' => $this->errorMessage,
            'is_valid' => $this->isValid,
            'original_address'  => $this->originalAddress->toArray(),
            'suggested_addresses' => $this->getSuggestedAddressToArray()
        ];
    }

    public function toDataObject()
    {
        $dataArray = $this->toArray();
        return new DataObject($dataArray);
    }

    protected function getSuggestedAddressToArray()
    {
        $suggested = [];
        foreach($this->suggestedAddresses as $address){
            $suggested[] = $address->toArray();
        }
        return $suggested;
    }
}
