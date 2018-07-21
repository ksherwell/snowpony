<?php

namespace Magesales\AddressValidation\Model;

use Magesales\AddressValidation\Model;
use Magesales\AddressValidation\Model\Validation\Address;
use Magesales\AddressValidation\Model\Validation\Response;
use Magesales\AddressValidation\Helper\Data;

use Magento\Framework\Model\AbstractModel;
use Magento\Directory\Model\ResourceModel\Region;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\ObjectManagerInterface;

abstract class AbstractValidation extends AbstractModel
{
    protected $address;
    protected $response;

    protected $helper;

    protected $objectManager;

    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        Address $address,
        Response $response,
        ObjectManagerInterface $objectManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->response = $response;
        $this->address = $address;
    }

    abstract protected function validateAddress();

    abstract public function getEnable();

    /*
     * @return \Magesales\AddressValidation\Model\Validation\Response
     */
    public function getValidationResponse()
    {
        return $this->response;
    }

    public function validate()
    {
        try {
            $this->checkAddressData();
            $this->validateAddress();
        } catch (\Exception $e) {
            $this->response->addError($e->getMessage());
        }
    }

    /*
     * @param \Magesales\AddressValidation\Model\Validation\Address|array $address
     */
    public function setAddressForValidation($address)
    {
        if(is_array($address)){
            $this->address->setData($address);
        } else {
            $this->address = $address;
        }

        $this->response->setOriginAddress($this->address);
    }

    /*
     * @return \Magesales\AddressValidation\Model\Validation\Address
     */
    public function getAddressForValidation()
    {
        return $this->address;
    }

    public function checkAddressData()
    {
        $this->getAddressForValidation()->checkAddressData();
    }

    /*
     * @param \Magesales\AddressValidation\Model\Validation\Address $address
     */
    public function addSuggestedAddress($address)
    {
        $address->updateRegionData();
        $isEqual = $address->isEqualWithAddress($this->getAddressForValidation());
        if(!$isEqual){
            if(!$this->isSuggestedAddressAdded($address)){
                $this->response->addSuggestedAddress($address);
            }
        } else {
            $this->response->setIsValid(true);
        }
    }

    protected function isSuggestedAddressAdded($address)
    {
        $suggestedAddresses = $this->response->getSuggestedAddresses();
        foreach($suggestedAddresses as $suggestedAddress){
            if($suggestedAddress->isEqualWithAddress($address)){
                return true;
            }
        }
        return false;
    }
}
