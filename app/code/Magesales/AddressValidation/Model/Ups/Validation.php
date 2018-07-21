<?php

namespace Magesales\AddressValidation\Model\Ups;

use Magesales\AddressValidation\Model\Ups\UpsAPI\USStreetLevelValidation;
use Magesales\AddressValidation\Model\Usps\USPSAddressVerify;

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

        $addressForCheck = $this->getAddressForValidation()->getData();
        $addressForCheck['zip5'] = $this->getAddressForValidation()->getZip5();
        $addressForCheck['zip4'] =$this->getAddressForValidation()->getZip4();
        $addressForCheck['region_code'] =$this->getAddressForValidation()->getRegionCode();

        $this->setupConfigUPS();

        $validation = new USStreetLevelValidation($addressForCheck);

        $xml = $validation->buildRequest();

        $response = $validation->sendRequest($xml);

        $this->validateResponse($response, $validation);

        $this->getUpsCandidates($response);
    }

    protected function validateResponse($response, $validation)
    {
        if (!isset($response['Response'])){
            throw new \Exception("Incorrect response from UPS API. [" . __CLASS__ . "] [" . __LINE__ . "]");
        }

        if (!isset($response['AddressKeyFormat']))
        {
            $errors = $validation->getResultsErrors();
            if (!empty($errors)){
                $err = implode('; ', $errors);
                throw new \Exception($err);
            }
        }
    }

    protected function setupConfigUPS()
    {
        $test_mode = $this->helper->getUpsTestMode();
        $login   = $this->helper->getUpsLogin();
        $pass   = $this->helper->getUpsPassword();
        $key   = $this->helper->getUpsAccessKey();

        if(empty($key) || empty($login) || empty($pass)){
            throw new \Exception("Empty UPS credentials.");
        }

        $GLOBALS ['ups_api']['access_key'] = $key;
        $GLOBALS ['ups_api']['developer_key'] = '';

        if ($test_mode)
        {
            $GLOBALS ['ups_api'] ['server'] = 'https://wwwcie.ups.com';
            $GLOBALS ['ups_street_level_api'] ['server'] = 'https://wwwcie.ups.com';
            // in other DOCS test server should be  https://wwwcie.ups.com/webservices/XAV
        } else {
            $GLOBALS ['ups_api'] ['server'] = 'https://www.ups.com';
            $GLOBALS ['ups_street_level_api'] ['server'] = 'https://onlinetools.ups.com';
            // in other DOCS live server should be  https://onlinetools.ups.com/webservices/XAV
        }

        $GLOBALS ['ups_api'] ['username'] = $login;
        $GLOBALS ['ups_api'] ['password'] = $pass;
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


    protected function getUpsCandidates($response)
    {
        if (!isset($response['AddressKeyFormat'])) {
            $this->response->setIsValid(false);
            return;
        }

        $addresses_array = $response['AddressKeyFormat'];
        if (isset($addresses_array['AddressClassification']))
        {
            $validCandidate = $this->parseUpsCandidate($addresses_array);
            $this->addSuggestedAddress($validCandidate);
        }
        else // we have list of addresses
        {
            foreach($addresses_array as $candidate)
            {
                $validCandidate = $this->parseUpsCandidate($candidate);
                $this->addSuggestedAddress($validCandidate);
                if($this->response->getIsValid()){
                    break;
                }
            }
        }
    }

    protected function parseUpsCandidate($candidate)
    {

        if (!isset($candidate['PostcodePrimaryLow']) ||
            !isset($candidate['AddressLine']) ||
            !isset($candidate['PoliticalDivision2']) ||
            !isset($candidate['PoliticalDivision1']) ||
            !isset($candidate['PostcodePrimaryLow'])
        ) {
            return false;
        }

        $address = $this->objectManager->create('\Magesales\AddressValidation\Model\Validation\Address');

        $address->setStreet($candidate['AddressLine']);
        $address->setCity($candidate['PoliticalDivision2']);
        $address->setRegion('');
        $address->setCountryId('US');
        $address->setRegionCode($candidate['PoliticalDivision1']);

        $postcode = $candidate['PostcodePrimaryLow'];
        if (isset($candidate['PostcodeExtendedLow']) && !empty($candidate['PostcodeExtendedLow'])) {
            $postcode .= '-' . $candidate['PostcodeExtendedLow'];
        }
        $address->setPostcode($postcode);

        if($this->helper->getUpsShowAddressType()) {
            $classCode = '';
            if (isset($candidate['AddressClassification']['Code'])) {
                $classCode = $candidate['AddressClassification']['Code'];
                $address->setClassCode($classCode);
            }
            if (isset($candidate['AddressClassification']['Description'])) {
                $description = $candidate['AddressClassification']['Description'];
                if (empty($description) && !empty($classCode)) {
                    $description = $classCode == 1 ? 'Commercial' :
                        $classCode == 2 ? 'Residential' : "";
                }
                if($description != 'Unknown') {
                    $address->setClassDescription($description);
                }
            }
        }

        return $address;
    }

    public function getEnable()
    {
        $login   = $this->helper->getUpsLogin();
        $pass   = $this->helper->getUpsPassword();
        $key   = $this->helper->getUpsAccessKey();

        return !(empty($key) || empty($login) || empty($pass));
    }
}