<?php

namespace Magesales\AddressValidation\Model\Usps;

class Validation extends \Magesales\AddressValidation\Model\AbstractValidation
{
    protected $addressMap = [
        'country_id' => 'country_id',
        'street'     => 'street',
        'city'       => 'city',
        'postcode'   => 'postcode',
        'region_id'  => 'region_id',
        'region'     => 'region'
    ];

    public function validateAddress()
    {
        $this->skipCountries();
        $this->skipRegions();

        $uspsAddress = $this->convertAddressToUSPSAddress();

        $validation = $this->getUspsVerifier();
        $validation->addAddress($uspsAddress);
        $validation->verify();

        $this->validateResponse($validation);
        $this->parseResponse($validation);
    }

    protected function convertAddressToUSPSAddress()
    {
        $addressForCheck = $this->getAddressForValidation();//->getData();

        $street = $addressForCheck->getStreet();
        $apt = ""; //TODO:
        $zip4 = $addressForCheck->getZip4();
        $zip5 = $addressForCheck->getZip5();
        $state = $addressForCheck->getRegionCode();
        $city = $addressForCheck->getCity();

        $uspsAddress = new USPSAddress();
        $uspsAddress->setApt($apt)
            ->setAddress($street)
            ->setCity($city)
            ->setState($state)
            ->setZip5($zip5)
            ->setZip4($zip4);

        $company = $addressForCheck->getCompany();
        if (!empty($company)) {
            $uspsAddress->setFirmName($company);
        }

        return $uspsAddress;
    }

    protected function skipRegions(){
        $regionName = $this->getAddressForValidation()->getRegion();

        $stateForSkip = array('virgin islands', 'puerto rico', 'guam');
        $reg = strtolower($regionName);
        if (in_array($reg, $stateForSkip)) {
            throw new \Exception("Skipped region <{$regionName}>.");
        }
    }

    protected function skipCountries(){
        $countryId = $this->getAddressForValidation()->getCountryId();
        $countryId = strtolower($countryId);
        if ($countryId != 'us') {
            throw new \Exception("Only addresses from USA can be checked");
        }
    }


    protected function getUspsVerifier(){
        $testMode = $this->helper->getUspsTestMode();
        $key = $this->helper->getUspsAccountId();

        if(empty($key)){
            throw new \Exception("Empty USPS Account ID.");
        }

        $verify = new USPSAddressVerify($key);
        $verify->setTestMode($testMode);

        return $verify;
    }


    protected function validateResponse($verify)
    {
        if (!$verify->isSuccess())
        {
            $errorCode = $verify->getErrorCode();
            $errorMessage = $verify->getErrorMessage();
            throw new \Exception('Error [' . strtolower($errorCode) . ']: ' . $errorMessage);
        }

        $response = $verify->getArrayResponse();
        if (!isset($response['AddressValidateResponse']) || !isset($response['AddressValidateResponse']['Address'])){
            throw new \Exception("Incorrect response from USPS API. [" . __CLASS__ . "] [" . __LINE__ . "]");
        }
    }

    protected function parseResponse($verify)
    {
        $response = $verify->getArrayResponse();

        $candidate = $response['AddressValidateResponse']['Address'];
        $validCandidate = $this->parseUspsCandidate($candidate);

        $this->addSuggestedAddress($validCandidate);
        if($this->response->getIsValid()){
            return;
        }
    }

    protected function parseUspsCandidate($candidate)
    {
        $address = $this->objectManager->create('\Magesales\AddressValidation\Model\Validation\Address');

        if (!isset($candidate['Address2']) || empty($candidate['Address2']) ||
            !isset($candidate['City']) || empty($candidate['City']) ||
            !isset($candidate['State']) || empty($candidate['State']) ||
            !isset($candidate['Zip5']) || empty($candidate['Zip5'])
        ) {
            return false;
        }

        $postcode = $candidate['Zip5'];
        if (isset($candidate['Zip4']) && !empty($candidate['Zip4'])) {
            $postcode .= '-' . $candidate['Zip4'];
        }

        $street = $candidate['Address2'];
        if (isset($candidate['Address1']) && !empty($candidate['Address1'])) {
            $street .= ' ' . $candidate['Address1'];
        }

        $address->setStreet($street);
        $address->setCity($candidate['City']);
        $address->setPostcode($postcode);
        $address->setRegion('');
        $address->setCountryId('US');
        $address->setRegionCode($candidate['State']);

        return $address;
    }

    public function getEnable()
    {
        $key = $this->helper->getUspsAccountId();
        return !empty($key);
    }
}