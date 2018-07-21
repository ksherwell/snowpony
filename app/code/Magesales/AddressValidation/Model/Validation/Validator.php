<?php

namespace Magesales\AddressValidation\Model\Validation;

use Magesales\AddressValidation\Helper\Data;
use Magesales\AddressValidation\Model\Google;

use Magento\Framework\Model\AbstractModel;
use Magento\Directory\Model\ResourceModel\Region;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Validator extends AbstractModel
{
    protected $helper;

    protected $upsValidator;
    protected $uspsValidator;
    protected $googleValidator;

    protected $mode;

    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $regCollectionFactory,
        CountryFactory $countryFactory,
        Data $helper,
        \Magesales\AddressValidation\Model\Ups\Validation $upsValidator,
        \Magesales\AddressValidation\Model\Usps\Validation $uspsValidator,
        \Magesales\AddressValidation\Model\Google\Validation $googleValidator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_regionCollectionFactory = $regCollectionFactory;
        $this->_countryFactory = $countryFactory;
        $this->helper = $helper;

        $this->googleValidator = $googleValidator;
        $this->upsValidator = $upsValidator;
        $this->uspsValidator = $uspsValidator;

        $this->mode = $this->helper->getValidationMode();
    }

    public function getValidationMode()
    {
        return $this->mode;
    }

    public function setValidationMode($mode)
    {
        $this->mode = $mode;
    }

    /*
     * @return Magesales\AddressValidation\Model\Validation\AbstractValidation
     */
    public function getValidator()
    {
        $mode = $this->getValidationMode();

        switch($mode)
        {
            case 'ups':
                return $this->upsValidator;
            case 'usps':
                return $this->uspsValidator;
            case 'google':
                return $this->googleValidator;
            default:
                throw new \Exception('Validation mode <' . $mode . '> is not supported');
        }
    }
}
