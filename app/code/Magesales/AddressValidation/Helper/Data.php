<?php

namespace Magesales\AddressValidation\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_encrypted;

    const ENABLE                  = 'addressvalidation/general/enable';
    const ALLOW_NOT_VALID_ADDRESS = 'addressvalidation/general/allow_not_valid_address';

    const VALIDATION_MODE         = 'addressvalidation/api_settings/mode';

    const CONTENT_HEADER            = 'addressvalidation/content/header';
    const CONTENT_MESSAGE           = 'addressvalidation/content/message';
    const CONTENT_ORIGIN_ADDRESS    = 'addressvalidation/content/origin_address';
    const CONTENT_SUGGESTED_ADDRESS = 'addressvalidation/content/suggested_address';

    const UPS_TEST_MODE           = 'addressvalidation/api_settings/ups_test_mode';
    const UPS_LOGIN               = 'addressvalidation/api_settings/ups_login';
    const UPS_PASSWORD            = 'addressvalidation/api_settings/ups_password';
    const UPS_ACCESS_KEY          = 'addressvalidation/api_settings/ups_access_key';
    const UPS_SHOW_ADDRESS_TYPE   = 'addressvalidation/api_settings/ups_show_address_type';

    const USPS_TEST_MODE          = 'addressvalidation/api_settings/usps_test_mode';
    const USPS_ACCOUNT_ID         = 'addressvalidation/api_settings/usps_account_id';

    const GOOGLE_API_KEY          = 'addressvalidation/api_settings/google_key';


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Config\Model\Config\Backend\Encrypted $encrypted
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\Config\Backend\Encrypted $encrypted
    ) {
        parent::__construct($context);
        $this->_encrypted = $encrypted;

    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->scopeConfig->getValue(self::ENABLE);
    }


    /**
     * @return mixed
     */
    public function getAllowNotValidAddress()
    {
        return $this->scopeConfig->getValue(self::ALLOW_NOT_VALID_ADDRESS);
    }

    /**
     * @return mixed
     */
    public function getValidationMode()
    {
        return $this->scopeConfig->getValue(self::VALIDATION_MODE);
    }

    /**
     * @return mixed
     */
    public function getUpsTestMode()
    {
        return $this->scopeConfig->getValue(self::UPS_TEST_MODE);
    }

    /**
     * @return mixed
     */
    public function getUpsLogin()
    {
        return $this->scopeConfig->getValue(self::UPS_LOGIN);
    }

    /**
     * @return string
     */
    public function getUpsPassword()
    {
        return $this->_encrypted->processValue($this->scopeConfig->getValue(self::UPS_PASSWORD));
    }

    /**
     * @return mixed
     */
    public function getUpsAccessKey()
    {
        return $this->scopeConfig->getValue(self::UPS_ACCESS_KEY);
    }

    /**
     * @return mixed
     */
    public function getUpsShowAddressType()
    {
        return $this->scopeConfig->getValue(self::UPS_SHOW_ADDRESS_TYPE);
    }

    /**
     * @return mixed
     */
    public function getUspsTestMode()
    {
        return $this->scopeConfig->getValue(self::USPS_TEST_MODE);
    }

    /**
     * @return mixed
     */
    public function getUspsAccountId()
    {
        return $this->scopeConfig->getValue(self::USPS_ACCOUNT_ID);
    }

    /**
     * @return mixed
     */
    public function getGoogleApiKey()
    {
        return $this->scopeConfig->getValue(self::GOOGLE_API_KEY);
    }

    /**
     * @return mixed
     */
    public function getContentHeader()
    {
        return $this->scopeConfig->getValue(self::CONTENT_HEADER);
    }

    /**
     * @return mixed
     */
    public function getContentMessage()
    {
        return $this->scopeConfig->getValue(self::CONTENT_MESSAGE);
    }

    /**
     * @return mixed
     */
    public function getContentOriginAddress()
    {
        return $this->scopeConfig->getValue(self::CONTENT_ORIGIN_ADDRESS);
    }

    /**
     * @return mixed
     */
    public function getContentSuggestedAddress()
    {
        return $this->scopeConfig->getValue(self::CONTENT_SUGGESTED_ADDRESS);
    }
}
