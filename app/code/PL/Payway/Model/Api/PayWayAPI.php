<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Payway\Model\Api;

class PayWayAPI
{
    public $url;
    public $logDirectory;
    public $proxyHost;
    public $proxyPort;
    public $proxyUser;
    public $proxyPassword;
    public $certFileName;
    public $initialised;
    public $caFile;

    public function __construct()
    {
        $this->url = null;
        $this->logDirectory = null;
        $this->initialised = false;
    }

    /**
     * @return bool
     * Returns true if this client object has been correctly intialised, or
     * false otherwise.
     */
    public function isInitialised()
    {
        return $this->initialised;
    }

    /**
     * @param $parameters
     * Initialise the client using the configuration initialisation parameters
     * (delimited with an ampersand &).  These parameters must contain at a
     * mimimum the url, the log directory and the certificate file.
     */
    public function initialise($parameters)
    {
        if ($this->initialised == true) {
            trigger_error("This client object has already been initialised", E_USER_ERROR);
        }

        // Parse the parameters into an array
        $props = $this->parseResponseParameters($parameters);

        // Check for the required properties
        if (!array_key_exists('logDirectory', $props)) {
            $this->handleInitialisationFailure(
                "Check initialisation parameters " .
                "(logDirectory) - You must specify the log directory"
            );
        }
        if (!array_key_exists('url', $props)) {
            $props[ 'url' ] = "https://ccapi.client.qvalent.com/payway/ccapi";
        }
        if (!array_key_exists('certificateFile', $props)) {
            $this->handleInitialisationFailure(
                "Check initialisation parameters " .
                "(certificateFile) - You must specify the certificate file"
            );
        }
        if (!array_key_exists('caFile', $props)) {
            $this->handleInitialisationFailure(
                "Check initialisation parameters " .
                "(caFile) - You must specify the Certificate Authority file"
            );
        }
        if (!array_key_exists('socketTimeout', $props)) {
            $props[ 'socketTimeout' ] = '60000';
        }

        // Set up the logging
        $logDir = $props[ 'logDirectory' ];
        if (!file_exists($logDir)) {
            mkdir($logDir, 0700, true);
        }
        if (!file_exists($logDir) || !is_dir($logDir)) {
            $this->handleInitialisationFailure(
                "Cannot use logging directory '" .  $logDir . "'"
            );
        }
        $this->logDirectory = $logDir;

        // Print information about the current environment
        $this->_log("<Init> Initialising PayWay API Client");
        /*
        $this->_log( "<Init> Using PHP version " . phpversion() );
        $extensions = get_loaded_extensions();
        foreach( $extensions as $extension )
        {
            $this->_log( "<Init> Loaded extension " . $extension );
        }
        */
        if (!is_numeric($this->_getProperty($props, "socketTimeout"))) {
            $this->handleInitialisationFailure(
                "Specified socket timeout '". $this->_getProperty($props, "socketTimeout")."' is not a number: "
            );
        }

        $this->url = $this->_getProperty($props, "url");
        $this->socketTimeout = (int)$this->_getProperty($props, "socketTimeout");

        $this->_log("<Init> URL = " . $this->url);
        $this->_log("<Init> socketTimeout = " . $this->socketTimeout . "ms");

        // Read the proxy information from the config
        $this->proxyHost = $this->_getProperty($props, "proxyHost");
        $this->proxyPort = $this->_getProperty($props, "proxyPort");
        $this->proxyUser = $this->_getProperty($props, "proxyUser");
        $this->proxyPassword = $this->_getProperty($props, "proxyPassword");
        if (!is_null($this->proxyHost) && !is_null($this->proxyPort)) {
            $this->_log("<Init> proxy = " . $this->proxyHost . ":" . $this->proxyPort);

            if (!is_numeric($this->proxyPort)) {
                $this->handleInitialisationFailure(
                    "Specified proxy port '" .
                    $this->proxyPort. "' is not a number: "
                );
            }

            if (!is_null($this->proxyUser)) {
                $this->_log("<Init> proxyUser = " . $this->proxyUser);
            }
            if (!is_null($this->proxyPassword)) {
                $this->_log(
                    "<Init> proxyPassword = " .
                    $this->_getStarString(strlen($this->proxyPassword))
                );
            }
        }

        // Load the certificate from the given file
        $this->certFileName = $this->_getProperty($props, "certificateFile");
        $this->_log("<Init> Loading certificate from file " . $this->certFileName);
        if (!file_exists($this->certFileName)) {
            $this->handleInitialisationFailure(
                "Certificate file does not exist: " . $this->certFileName
            );
        }
        if ($this->_readFile($this->certFileName) == null) {
            $this->handleInitialisationFailure(
                "Certificate file cannot be read: " . $this->certFileName
            );
        }
        $cert = openssl_x509_parse($this->_readFile($this->certFileName));
        $this->_log("<Init> Certificate serial number: " . strtoupper(dechex($cert['serialNumber'])));
        $this->_log("<Init> Certificate valid to: " . date('d-M-Y H:i:s', $cert['validTo_time_t']));

        // Load the CA certificates from the given file
        $this->caFile = $this->_getProperty($props, "caFile");
        $this->_log("<Init> Loading CA certificates from file " . $this->caFile);
        if (!file_exists($this->caFile)) {
            $this->handleInitialisationFailure(
                "Certificate Authority file does not exist: " . $this->caFile
            );
        }
        if ($this->_readFile($this->caFile) == null) {
            $this->handleInitialisationFailure(
                "CA file cannot be read: " . $this->caFile
            );
        }

        $this->initialised = true;
        $this->_log("<Init> Initialisation complete");
    }

    /**
     * @param $message
     * Convenience method to handle initialisation errors
     */
    public function handleInitialisationFailure($message)
    {
        if (!is_null($this->logDirectory)) {
            $this->_log("<Init> PayWay API Client initialisation failed: " . $message);
        }

        trigger_error("PayWay API Client initialisation failed: " . $message, E_USER_ERROR);
    }

    /**
     * Parse the response parameters string from the processCreditCard method into an array.
     * $parametersString is the response parameters string from the processCreditCard function.
     * The return value is an array which contains the parameter names as keys and the parameter values as values.
     * @param $parametersString
     * @return array
     */
    public function parseResponseParameters($parametersString)
    {
        // Split the message at the field breaks
        $parameterArray = explode("&", $parametersString);
        $props = [];

        // Loop through each parameter provided
        foreach ($parameterArray as $parameter) {
            list($paramName, $paramValue) = explode("=", $parameter);
            $props[urldecode($paramName)] = urldecode($paramValue);
        }

        return $props;
    }

    /**
     * @param $parametersArray
     * @return string
     */
    public function formatRequestParameters($parametersArray)
    {
        // Build the message for logging
        $parametersString = '';
        foreach ($parametersArray as $paramName => $paramValue) {
            if ($parametersString != '') {
                $parametersString = $parametersString . '&';
            }
            $parametersString = $parametersString . urlencode($paramName) . '=' . urlencode($paramValue);
        }
        return $parametersString;
    }

    /**
     * Main credit card processing method.  Pass the request parameters into
     * this method, then the current thread will wait for the response to be
     * returned from the server.
     * $requestText is the parameters string containing all the request fields
     * (delimited with an ampersand &amp;) to send to the server.
     * The return value is a string containing all the response fields (delimited
     * with an ampersand &amp;) from the server.
     * @param $requestText
     * @return mixed|string
     */
    public function processCreditCard($requestText)
    {
        if ($this->initialised == false) {
            return $this->_getResponseString("3", "QA", "This client has not been initialised!");
        }

        $orderNumber = $this->_getOrderNumber($requestText);
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set proxy information as required
        if (!is_null($this->proxyHost) && !is_null($this->proxyPort)) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyHost . ":" . $this->proxyPort);
            if (!is_null($this->proxyUser)) {
                if (is_null($this->proxyPassword)) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyUser . ":");
                } else {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyUser . ":" . $this->proxyPassword);
                }
            }
        }

        // Set timeout options
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->socketTimeout / 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->socketTimeout / 1000);

        // Set references to certificate files
        curl_setopt($ch, CURLOPT_SSLCERT, $this->certFileName);
        curl_setopt($ch, CURLOPT_CAINFO, $this->caFile);

        // Check the existence of a common name in the SSL peer's certificate
        // and also verify that it matches the hostname provided
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Verify the certificate of the SSL peer
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestText);

        $this->_log("<Request>  " .$orderNumber. " " .$this->_getMessageForLogging($requestText));
        $responseText = curl_exec($ch);
        $errorNumber = curl_errno($ch);
        if ($errorNumber != 0) {
            $responseText = $this->_getResponseString(
                "2",
                "QI",
                "Transaction Incomplete - contact your acquiring bank to confirm reconciliation"
            );
            $this->_log(
                "<Response> " .$orderNumber . " ERROR during processing: " .
                $this->_getMessageForLogging($responseText) .
                "\r\n  Error Number: " . $errorNumber . ", Description: '" .
                curl_error($ch) . "'"
            );
        } else {
            $this->_log("<Response> " .$orderNumber. " ". $this->_getMessageForLogging($responseText));
        }
        curl_close($ch);
        return $responseText;
    }

    /**
     * @param $message
     * @return mixed
     * Get the order number for the given request.
     */
    protected function _getOrderNumber($message)
    {
        // Parse the parameters into an array
        $parameters = $this->parseResponseParameters($message);

        return $parameters[ "customer.orderNumber" ];
    }

    /*
     * Generate a response string for the given response information when an
     * error occurs.
     */
    protected function _getResponseString($summaryCode, $responseCode, $responseText)
    {
        return "response.summaryCode=" . $summaryCode .
        "&response.responseCode=" . $responseCode .
        "&response.text=" . $responseText .
        "&response.transactionDate=" .
        strtoupper(date("d-M-y H:i:s"));
    }

    protected function _getProperty($props, $name)
    {
        if (array_key_exists($name, $props)) {
            return $props[$name];
        } else {
            return null;
        }
    }

    /**
     * @param $message
     * Write a message to today's log file
     */
    protected function _log($message)
    {
        list($usec, $sec) = explode(" ", microtime());
        $dtime = date("Y-m-d H:i:s." . sprintf("%03d", (int)(1000 * $usec)), $sec);
        $entry_line = $dtime . " " . $message . "\r\n";
        $filename = $this->logDirectory . "/" . "ccapi_" . date("Ymd") . ".log";
        $fp = fopen($filename, "a");
        fputs($fp, $entry_line);
        fclose($fp);
    }

    /**
     * @param $message
     * @return string
     * Get the request message in a format suitable for logging
     */
    public function _getMessageForLogging($message)
    {
        // Parse the parameters into an array
        $parameters = $this->parseResponseParameters($message);

        if (array_key_exists("card.PAN", $parameters)) {
            $card = $parameters[ "card.PAN" ];
            $parameters[ "card.PAN" ] =
                $this->_formatCardNumberForDisplay($card);
        }

        if (array_key_exists("card.CVN", $parameters)) {
            $cvn = $parameters[ "card.CVN" ];
            $parameters[ "card.CVN" ] =
                $this->_getStarString(strlen($cvn));
        }

        if (array_key_exists("card.expiryMonth", $parameters)) {
            $expiryMonth = $parameters[ "card.expiryMonth" ];
            $parameters[ "card.expiryMonth" ] =
                $this->_getStarString(strlen($expiryMonth));
        }

        if (array_key_exists("card.expiryYear", $parameters)) {
            $expiryYear = $parameters[ "card.expiryYear" ];
            $parameters[ "card.expiryYear" ] =
                $this->_getStarString(strlen($expiryYear));
        }

        if (array_key_exists("customer.password", $parameters)) {
            $customerPassword = $parameters[ "customer.password" ];
            $parameters[ "customer.password" ] =
                $this->_getStarString(strlen($customerPassword));
        }

        // Build the message for logging
        $logMessage = '';
        foreach ($parameters as $paramName => $paramValue) {
            $logMessage = $logMessage . $paramName . '=' . $paramValue . ';';
        }
        return $logMessage;
    }

    /**
     * @param $cardNumber
     * @return null|string
     * Format the card number to be displayed to a user or in a log file
     */
    protected function _formatCardNumberForDisplay($cardNumber)
    {
        if (is_null($cardNumber)) {
            return null;
        }

        $formattedCardNumber = '';
        if (strlen($cardNumber) >= 16) {
            $formattedCardNumber = substr($cardNumber, 0, 6) . "..." .
                substr($cardNumber, -3);
        } elseif (strlen($cardNumber) >= 14) {
            $formattedCardNumber = substr($cardNumber, 0, 4) . "..." .
                substr($cardNumber, -3);
        } else {
            $formattedCardNumber = $cardNumber;
        }
        return $formattedCardNumber;
    }

    /**
     * @param $length
     * @return string
     * Return a string of stars with the given length
     */
    protected function _getStarString($length)
    {
        $buf = '';
        for ($i = 0; $i < $length; $i++) {
            $buf = $buf . '*';
        }
        return $buf;
    }

    /**
     * @param $file
     * @return null|string
     * Read file and return data
     */
    protected function _readFile($file)
    {
        $reader = fopen($file, "r");
        if (!$reader) {
            return null;
        }
        $data = fread($reader, 8192);
        fclose($reader);
        return $data;
    }
}
