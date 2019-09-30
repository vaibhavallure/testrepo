<?php
class Teamwork_Universalcustomers_Model_Svs
{
    const UC_OPTIONS_PATH = 'teamwork_universalcustomers/options/path';
    const UC_OPTIONS_ACCESS_TOKEN = 'teamwork_universalcustomers/options/access_token';

    protected $_configWebsiteId = null;

    const API_SVS_CHECK_METHOD          = 'checkbyemail';
    const API_SVS_CHECK_PHONE_METHOD    = 'checkbyphone';
    const API_SVS_GET_METHOD            = 'get';
    const API_SVS_REGISTER_METHOD       = 'register';
    const API_SVS_UPDATE_METHOD         = 'update';
    const API_SVS_LOGIN_METHOD          = 'authbyemail';
    const API_SVS_GETNAMESPACE_METHOD   = 'getnamespace';

    const API_SVS_LOG_FILE = 'uc.log';
    const API_SVS_LOG_EXCEPTION_FILE = 'uc_exception.log';

    const LAST_SVS_SAVED_ADDRESSES = 'LAST_SVS_SAVED_ADDRESSES';

    protected $_apiName = 'ecommapi';
    protected $_allowedErrorCodes = array(9);

    public function checkCustomer($email)
    {
        $params = array(
            'email' => $email,
        );
        $response = $this->request($params, self::API_SVS_CHECK_METHOD);
        return $response['customer_id'];
    }

    public function getCustomer($ucGuid)
    {
        $params = array(
            'customer_id' => $ucGuid,
        );
        return $response = $this->request($params, self::API_SVS_GET_METHOD);
    }

    public function updateCustomer($customerData, $skipChecker=false)
    {
        if( Mage::helper('teamwork_universalcustomers')->checkUpdateNeeded($customerData) || $skipChecker )
        {
            $response = $this->request($customerData, self::API_SVS_UPDATE_METHOD);
        }
    }

    public function loginCustomer($login, $password)
    {
        $loginData = array(
            'email'     => $login,
            'password'  => $password
        );

        /*New Code from Allure for Login Log*/
        $encrypted_data = $this->getEncryptedData($loginData);
        $this->writeLog('----- Customer Login Request -----');
        $this->writeLog($encrypted_data);

        $response = $this->request($loginData, self::API_SVS_LOGIN_METHOD);

        $this->writeLog('----- Response from TW -----');
        $this->writeLog($response);
        $this->writeLog('----- Customer Login Request End-----');

        return !empty( $response['email'] ) ? $response : null;
    }

    public function registerCustomer($customerData)
    {
        $response = $this->request($customerData, self::API_SVS_REGISTER_METHOD);
        return $response['customer_id'];
    }

    protected function _getFullUri($functionName)
    {
        $parts = array( rtrim(Mage::getStoreConfig(self::UC_OPTIONS_PATH), '/'), $this->_apiName, $this->getAreaName($functionName), $functionName );
        return trim(implode('/', $parts));
    }

    protected function request($params, $functionName)
    {
        $header = array(
            'Content-type: application/json',
            'Access-Token: ' . $this->_getToken()
        );

        $http = new Varien_Http_Adapter_Curl();
        $http->setConfig(array('header' => 0));

        $http->write(Zend_Http_Client::POST,  $this->_getFullUri($functionName), '1.1', $header, json_encode($params));
        $return = $http->read();

        if( !empty($params['password']) )
        {
            $params['password'] = str_repeat("*", strlen($params['password']));
        }
        Mage::log($functionName, null, self::API_SVS_LOG_FILE);
        Mage::log(var_export($params,1), null, self::API_SVS_LOG_FILE);

        if(stripos($return, 'HTTP/1.1') == 0) // bug magento 1.5
        {
            $response = explode("\r\n\r\n", $return);
            $return = end($response);
        }

        return $this->errorHandler(json_decode($return,1),$http);
    }

    protected function getAreaName($functionName)
    {
        $areas = array(
            'customers' => array(
                self::API_SVS_CHECK_METHOD,
                self::API_SVS_CHECK_PHONE_METHOD,
                self::API_SVS_GET_METHOD,
                self::API_SVS_REGISTER_METHOD,
                self::API_SVS_UPDATE_METHOD,
                self::API_SVS_LOGIN_METHOD
            ),
            'devices' => array(
                self::API_SVS_GETNAMESPACE_METHOD,
            )
        );
        foreach($areas as $area => $methods)
        {
            if( array_search($functionName, $methods) !== FALSE )
            {
                return $area;
            }
        }

        $exception = new Mage_Core_Exception();
        $exception->setMessage( $this->__( "There is no area name for SVS::{$function}()" ) );
    }

    protected function errorHandler($response, $http)
    {
        if (empty($response['error']))
        {
            Mage::log($response, null, self::API_SVS_LOG_FILE);
        }
        else
        {
            Mage::log($response, null, self::API_SVS_LOG_EXCEPTION_FILE);
        }

        if ($http->getInfo(CURLINFO_HTTP_CODE) != '200')
        {
            Mage::log("HTTP CODE: {$http->getInfo(CURLINFO_HTTP_CODE)}", null, self::API_SVS_LOG_EXCEPTION_FILE);
            Mage::getSingleton('teamwork_universalcustomers/thrower')->errorThrower();
        }

        if (!empty($response['error']) && !in_array($response['errorCode'],  $this->_allowedErrorCodes))
        {
			Mage::log("DATA: {".json_encode($response)."}", null, self::API_SVS_LOG_EXCEPTION_FILE);
			/*
            Mage::getSingleton('teamwork_universalcustomers/thrower')->errorThrower(
                array(
                    'code'      => $response['errorCode'],
                    'message'   => $response['message']
                )
            );
			*/
        }

        return $response;
    }

	public function getNamespace()
    {
        try
        {
            $params = array();
            return $this->request($params, self::API_SVS_GETNAMESPACE_METHOD);
        }
        catch(Exception $e)
        {}
    }

    public function setWebsiteId($websiteId)
    {
        $this->_configWebsiteId = $websiteId;
        return $this;
    }

    protected function _getToken()
    {
        return $this->_getConfig(self::UC_OPTIONS_ACCESS_TOKEN);
    }

    protected function _getConfig($path)
    {
        if (!is_null($this->_configWebsiteId)) {
            $website = Mage::app()->getWebsite($this->_configWebsiteId);

            $node = (Mage::getConfig()->getNode("websites/{$website->getCode()}/" . $path));
            $value = (string)$node;
            if (!empty($node['backend_model']) && !empty($value))
            {
                $backend = Mage::getModel((string) $node['backend_model']);
                $backend->setPath($path)->setValue($value)->afterLoad();
                return $backend->getValue();
            }
            else
            {
                return $website->getConfig($path);
            }
        }
        return Mage::getStoreConfig($path);
    }

	protected function multiRequest($params, $functionName)
    {
        $header = array(
            'Content-type: application/json',
            'Access-Token: ' . $this->_getToken()
        );

        $url = $this->_getFullUri($functionName);
        Mage::log($functionName, null, self::API_SVS_LOG_FILE);

        $options = array();
        foreach($params as $key => $param)
        {
            $options[$key][CURLOPT_HTTPHEADER] = $header;
            $options[$key][CURLOPT_POST] = true;
            $options[$key][CURLOPT_SSL_VERIFYHOST] = false;
            $options[$key][CURLOPT_SSL_VERIFYPEER] = false;
            $options[$key][CURLOPT_POSTFIELDS] = json_encode($param);

            if( !empty($param['password']) )
            {
                $param['password'] = str_repeat("*", strlen($param['password']));
            }
            Mage::log(var_export($param,1), null, self::API_SVS_LOG_FILE);
        }

        $return = Mage::getModel('teamwork_universalcustomers/curl')->multiRequest($url, $options);

        Mage::log($return, null, 'entity.log');

        return $return;
    }

    public function checkCustomerMulty($emails)
    {
        $params = array();
        foreach($emails as $customer_id => $email)
        {
            $params[$customer_id] = array(
                'email' => $email,
            );
        }

        $curlInfo = $this->multiRequest($params, self::API_SVS_CHECK_METHOD);
        $response = array();
        foreach($curlInfo as $email => $data)
        {
            $data = json_decode($data,1);
            $response[$email] = !empty($data['customer_id']) ? $data['customer_id'] : null;
        }
        return $response;
    }

    public function updateCustomerMulty($customer)
    {
        $this->multiRequest($customer, self::API_SVS_UPDATE_METHOD);
    }

    public function registerCustomerMulty($customer)
    {
        $this->multiRequest($customer, self::API_SVS_REGISTER_METHOD);
    }

    public function getCustomerMulty($ucGuids)
    {
        $params = array();
        foreach($ucGuids as $customer_id => $customer_guid)
        {
            $params[$customer_id] = array(
                'customer_id' => $customer_guid,
            );
        }

        $curlInfo = $this->multiRequest($params, self::API_SVS_GET_METHOD);
        $response = array();
        foreach($curlInfo as $customer_id => $data)
        {
            $data = json_decode($data,1);
            $response[$customer_id] = !empty($data['customer_id']) ? $data['customer_id'] : null;
        }
        return $response;
    }

    /*Encrypted Login Data*/
    public function getEncryptedData($loginData){
        $encrypted_password = isset($loginData['password'])?$loginData['password']:'';
        $encrypted_password = base64_encode($encrypted_password);
        $loginData['password'] = $encrypted_password;
        return $loginData;
    }
    /*Log function to track responses of Login*/
    public function writeLog($message){
        Mage::log($message,Zend_Log::DEBUG,'login.log',true);
    }
}
