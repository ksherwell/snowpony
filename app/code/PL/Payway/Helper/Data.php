<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $text
     * @return \Magento\Framework\Phrase
     */
    public function wrapGatewayError($text)
    {
        return __('Gateway error: %1', $text);
    }

    public function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }

    public function decryptParameters( $base64Key, $encryptedParametersText, $signatureText )
    {
        $key = base64_decode( $base64Key );
        $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
        $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');

        // Decrypt the parameter text
        mcrypt_generic_init($td, $key, $iv);
        $parametersText = mdecrypt_generic($td, base64_decode( $encryptedParametersText ) );
        $parametersText = $this->pkcs5Unpad( $parametersText );
        mcrypt_generic_deinit($td);

        // Decrypt the signature value
        mcrypt_generic_init($td, $key, $iv);
        $hash = mdecrypt_generic($td, base64_decode( $signatureText ) );
        $hash = bin2hex( $this->pkcs5Unpad( $hash ) );
        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        // Compute the MD5 hash of the parameters
        $computedHash = md5( $parametersText );

        // Check the provided MD5 hash against the computed one
        if ( $computedHash != $hash )
        {
            trigger_error( "Invalid parameters signature" );
        }

        $parameterArray = explode( "&", $parametersText );
        $parameters = array();

        // Loop through each parameter provided
        foreach ( $parameterArray as $parameter )
        {
            list( $paramName, $paramValue ) = explode( "=", $parameter );
            $parameters[ urldecode( $paramName ) ] = urldecode( $paramValue );
        }
        return $parameters;
    }
}
