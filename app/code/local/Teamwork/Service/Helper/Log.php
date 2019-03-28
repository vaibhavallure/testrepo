<?php
class Teamwork_Service_Helper_Log extends Mage_Core_Helper_Abstract
{
    const LOG_FILE = 'teamwork_service.log';

    /**
     * Logs API method call.
     *
     * @param  string $method
     * @param  mixed $params
     */
    public static function logApiRequest($method, $params)
    {
        // log API call
        if (Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_LOG_API_REQUESTS))
        {
            // if params are supposed to be base64-decoded, log decode result
            $preparedParams = (self::_isBase64Encoded($params)) ? base64_decode($params) : $params;

            $methodParts = explode('::',$method);
            $logText =  "Starting API method '" . end($methodParts) ."' (PID " . getmypid() . ")\n";
            $logText .= "Params:\n" . $preparedParams. "\n";
            
            Mage::log($logText, null, self::LOG_FILE);
        }
    }

    /**
     * Logs API method response.
     *
     * @param  string $method
     * @param  array $params
     */
    public static function logApiResponse($method, $params, $response)
    {
        if (Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_LOG_API_RESPONSES))
        {
            $methodParts = explode('::',$method);
            $logText  = "Finishing API method '" . end($methodParts)  ."' (PID " . getmypid() . ")\n";
            $logText .= "-- Response:\n" . var_export(base64_decode($response), true) . "\n";

            Mage::log($logText, null, self::LOG_FILE);
        }
    }

    /**
     * Returns TRUE if given value is supposed to be a base64-encoded string
     *
     * @param mixed $value
     *
     * @return boolean
     */
    protected static function _isBase64Encoded($value)
    {
        return (isset($value) && is_string($value) && preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value));
    }
    
    /**
     * Add message to log
     *
     * @param string $msg
     * @param int $level
     * @param string $file
     */
    public function addMessage($msg, $level = null, $file = '')
    {
        $level  = is_null($level) ? Zend_Log::DEBUG : $level;
        $file = empty($file) ? self::LOG_FILE : $file;
        Mage::log($msg, $level, $file);
    }
}