<?php

namespace Magesales\AddressValidation\Model\Google;

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
        $response = $this->apiRequest();
        return $this->getGoogleCandidates($response);
    }

    protected function apiRequest()
    {
        $address = $this->getAddressAsUrlParam();
        $protocol = 'https';
        $key = $this->helper->getGoogleApiKey();

        if (empty($key)) {
            throw new \Exception("Empty Google API key.");
        }

        $url = sprintf("%s://maps.google.com/maps/api/geocode/json?address=%s&key=%s", $protocol, $address, $key);

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $responseJson = @file_get_contents($url, false, stream_context_create($arrContextOptions));
        return json_decode($responseJson, true);
    }

    protected function getAddressAsUrlParam()
    {
        $map = [
            'street',
            'country_id',
            'city',
            'postcode',
            'region'
        ];

        $address = $this->getAddressForValidation()->toArray($map);

        $address = implode(',', $address);
        return urlencode($address);
    }

    protected function getGoogleCandidates($response)
    {
        if(isset($response['results']) && $response['status']=='OK')
        {
            foreach ($response['results'] as $candidate) {
                if(!isset($candidate['address_components'])){
                    continue;
                }

                $address = $this->getAddressCandidate($candidate['address_components']);
                if(empty($address)){
                    continue;
                }

                $this->addSuggestedAddress($address);

                if($this->response->getIsValid()){
                    break;
                }
            }
        }
    }


    protected function getAddressCandidate($candidate)
    {
        $address = $this->objectManager->create('\Magesales\AddressValidation\Model\Validation\Address');

        $street_number = '';
        $route = '';
        foreach ($candidate as $component) {
            if (isset($component['types'][0]) && isset($component['long_name']) && isset($component['short_name'])) {
                $id = $component['types'][0];
                $valueLong = trim($component['long_name']);
                $valueShort = trim($component['short_name']);

                switch($id)
                {
                    case "postal_code":
                        if(empty($valueLong)) return null;
                        $address->setPostcode($valueLong);
                        break;
                    case "country":
                        if(empty($valueLong)) return null;
                        $address->setCountryId($valueShort);
                        break;
                    case "administrative_area_level_1":
                        if(empty($valueLong) && empty($valueShort)) return null;
                        $address->setRegion($valueLong);
                        $address->setRegionCode($valueShort);
                        break;
                    case "locality":
                        if(empty($valueLong)) return null;
                        $address->setCity($valueLong);
                        break;
                    case "street_number":
                        $street_number = $valueLong;
                        break;
                    case "route":
                        $route = $valueLong;
                        break;
                }
            }
        }

        if(empty($street_number) && empty($route)) return null;
        $address->setStreet($street_number . ' ' . $route);

        return $address;
    }

    public function getEnable()
    {
        $key = $this->helper->getGoogleApiKey();
        return !empty($key);
    }
}
