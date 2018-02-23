<?php

class Teamwork_CEGiftcards_Model_Svs extends Mage_Core_Model_Abstract
{
    protected $_debug_to_log    = false;

    protected $_configWebsiteId = null;

    const API_NAME  = 'ecommapi';
    
    const API_SVS_GIFTCARDS_GET_METHOD          = 'get';
    const API_SVS_GIFTCARDS_SALE_METHOD         = 'sale';
    const API_SVS_GIFTCARDS_CREATE_METHOD       = 'create';
    const API_SVS_GIFTCARDS_GETNAMESPACE_METHOD = 'getnamespace';

    const CONFIG_PATH_ACCESS_TOKEN        = 'teamwork_cegiftcards/general/token';
    const CONFIG_PATH_URL                 = 'teamwork_cegiftcards/general/url';
    const CONFIG_PATH_LOG_SVS_REQUESTS    = 'teamwork_cegiftcards/general/log_svs_requests';
    
    
    
    const SVS_ERROR_CODE_GIFTCARD_ALREADY_EXISTS = 10;


/*    public function _construct()
    {
        if(
            !$this->_getToken() ||
            !Mage::getStoreConfig(self::CONFIG_PATH_URL)
        )
        {
            throw new Teamwork_CEGiftcards_Model_Exception('Please, fill full payment information');
        }
    }*/

    protected function _getToken()
    {
        return $this->_getConfig(self::CONFIG_PATH_ACCESS_TOKEN);
    }

    public function getGiftcardData($giftcardNo, $giftcardPin=false)
    {
        $this->_validateConfiguration();
        //try{
            $result = $this->_get($giftcardNo, $giftcardPin);
            return $result['giftcard'];
        //} catch(Teamwork_CEGiftcards_Model_Exception $e) {
        //    return null;
        //}
    }

   /* public function isGiftcardExists($giftcardNo)
    {
        $this->_validateConfiguration();
        if ($this->getGiftcardData($giftcardNo)) {
            return true;
        }
        return false;
   }*/

    public function getNamespace()
    {
        $token = $this->_getToken();
        if($token)
        {
            $params = array();
            return $this->_request($params, self::API_SVS_GIFTCARDS_GETNAMESPACE_METHOD);
        }
    }
    
    public function giftcardBalance($giftcardNo)
    {
        if ($result = $this->getGiftcardData($giftcardNo)) {
            return $result['giftcard_balance'];
        }
        return false;
    }

    public function sale($giftcardNo, $giftcardPin, $amount, $force = false)
    {
        $this->_validateConfiguration();
        $result = $this->_sale($giftcardNo, $giftcardPin, $amount, $force);
        return $result['transaction_id'];
    }

    protected function _request($params, $functionName)
    {
        $header = array(
            'Content-type: application/json',
            'Access-Token: ' . $this->_getToken()
        );

        $params = json_encode($params);

        $http = new Varien_Http_Adapter_Curl();
        $http->setConfig(array('header' => 0));

        $http->write(Zend_Http_Client::POST,  $this->_getFullSvsUrl($functionName), '1.1', $header, $params);
        $return = $http->read();

        if ((bool)$this->_getConfig(self::CONFIG_PATH_LOG_SVS_REQUESTS)) {
            $this->_logDebug("request url: " . $this->_getFullSvsUrl($functionName) . "; request params: " . $params . "; response: " . $return);
        }

        if(stripos($return, 'HTTP/1.1') == 0) // bug magento 1.5
        {
            $response = explode("\r\n\r\n", $return);
            $return = end($response);
        }

        return json_decode($return, 1);
    }

    protected function _get($giftcardNo, $giftcardPin=false)
    {
        $params = array("giftcard_id" => $giftcardNo);

        if (Mage::helper('teamwork_cegiftcards')->pinIsEnabled()
            && Mage::getModel('checkout/cart')->getQuote()->getStoreId() != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $params['pin'] = (string)$giftcardPin;
        }

        $result = $this->_request($params, self::API_SVS_GIFTCARDS_GET_METHOD);

        if (!$this->_validateResponse($result)
            || !array_key_exists('giftcard', $result)
            || (!is_null($result['giftcard'])
                    && (!array_key_exists('giftcard_balance',$result['giftcard'])
                        || !array_key_exists('active',$result['giftcard'])
                    )
               )

            ) {
                $this->_logError($result, $giftcardNo);
                throw new Teamwork_CEGiftcards_Model_Exception_Svs_Communication("Error occured while SVS _get requesting");
        }


        if (is_null($result['giftcard'])) {
            throw new Teamwork_CEGiftcards_Model_Exception_Svs_Response(
                Mage::helper("teamwork_cegiftcards")->__('Gift card "%s" doesn\'t exists', Mage::helper('core')->escapeHtml($giftcardNo))
            );
        }

        $result['giftcard']['giftcard_balance'] = floatval($result['giftcard']['giftcard_balance']);
        return $result;
    }

    protected function _sale($giftcardNo, $giftcardPin, $amount, $force = false)
    {
       // Mage::getVersion();
        $params = array(
            "giftcard_id" => $giftcardNo,
            //"amount" => number_format(floatval($amount), 2, '.', ''),
            "amount" => (string)$amount,
            "transaction_source" => "Magento v." . Mage::getVersion(),
            //"local_transaction_time" => Mage::getModel('core/date')->date('r'),
            "local_transaction_time" => date('r'),
        );
        if ($force) {
            $params['forced_recharge'] = true;
        }

        if (Mage::helper('teamwork_cegiftcards')->pinIsEnabled()
            && Mage::getModel('checkout/cart')->getQuote()->getStoreId() != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $params['pin'] = (string)$giftcardPin;
        }

        $result = $this->_request($params, self::API_SVS_GIFTCARDS_SALE_METHOD);


        if (!$this->_validateResponse($result)
            || !array_key_exists('transaction_id', $result)
            || !array_key_exists('giftcard', $result)
            || (!is_null($result['giftcard'])
                    && !array_key_exists('giftcard_balance',$result['giftcard'])
                )
            ) {
                $this->_logError($result, $giftcardNo);
                throw new Teamwork_CEGiftcards_Model_Exception_Svs_Communication("Error occured while SVS sale requesting");
        }

        if (is_null($result['giftcard'])) {
            throw new Teamwork_CEGiftcards_Model_Exception_Svs_Response(
                Mage::helper("teamwork_cegiftcards")->__('Gift card "%s" doesn\'t exists', Mage::helper('core')->escapeHtml($giftcardNo))
            );
        }

        return $result;
    }

    protected function _validateResponse($response)
    {
        if (!$response || isset($response['errorCode'])) {
            return false;
        }
        return true;
    }

    protected function _logError($result, $giftcardNo, $additionalData = "")
    {
        $errorMsg = "SVS Error: giftcardNo: " . $giftcardNo . ": ";
        if (!$result) {
            $errorMsg = "Wrong SVS response";
        } else {
            $msg = "";
            if (isset($result['errorCode'])) {
                $msg = " code: " . $result['errorCode'];
            }
            if (isset($result['error'])) {
                $msg = " error: " . $result['error'];
            }
            if (isset($result['data'])) {
                $msg = " data: " . $result['data'];
            }
            if (!$msg) $errorMsg .= (string)$result;
            else $errorMsg .= $msg;
        }
        Mage::helper('teamwork_cegiftcards/log')->addMessage($errorMsg . $additionalData);
    }

    protected function _logDebug($msg)
    {
        Mage::helper('teamwork_cegiftcards/log')->addMessage("SVS debug: " . $msg);    
    }

    static public function negative($amount)
    {
        if($amount > 0) {
            return '-'.$amount;
        }
        return $amount;
    }

    protected function _getFullSvsUrl($functionName)
    {
        return rtrim($this->_getConfig(self::CONFIG_PATH_URL), "//") . '/' . self::API_NAME . '/' . $this->_getAreaName($functionName) . '/' . trim($functionName, "//");
    }

    public function create($giftcardNo, $giftcardPin, $amount, $dateExpires = null)
    {
        $this->_validateConfiguration();
        $params = array(
            "giftcard_id" => $giftcardNo,
            "giftcard_balance" => (string)$amount,
            "transaction_source" => "Magento v." . Mage::getVersion(),
            //"customer_id" => $customerId,
            "local_transaction_time" => date('r'),
        );
        if (!is_null($dateExpires)) {
            $params['expiration_time'] = $dateExpires * 1000;
        }

        if (Mage::helper('teamwork_cegiftcards')->pinIsEnabled()
            && Mage::helper('teamwork_cegiftcards')->pinGenerationIsEnabled()) {
            $params['pin'] = $giftcardPin;
        }

        $result = $this->_request($params, self::API_SVS_GIFTCARDS_CREATE_METHOD);

        if (!$this->_validateResponse($result)) {
            $this->_logError($result, $giftcardNo);
            if (array_key_exists("errorCode", $result)) {
                //todo: fix this message should not be seen on frontend
                throw new Teamwork_CEGiftcards_Model_Exception_Svs_Response("Error occured while SVS create requesting", $result["errorCode"]);
            }
            throw new Teamwork_CEGiftcards_Model_Exception_Svs_Communication("Error occured while SVS create requesting");
        }
        return $result;
    }

    public function setWebsiteId($websiteId)
    {
        $this->_configWebsiteId = $websiteId;
        return $this;
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

    protected function _validateConfiguration()
    {
        if(
            !$this->_getToken() ||
            !$this->_getConfig(self::CONFIG_PATH_URL)
        )
        {
            $e = new Teamwork_CEGiftcards_Model_Exception('Please, fill full GC information');
            //if (Mage::app()->getStore(null)->getCode() !== "admin") $e->isVisibleOnFrontend(false);
            throw $e;
        }
    }
    
    protected function _getAreaName($functionName)
    {
        $areas = array(
            'giftcards' => array(
                self::API_SVS_GIFTCARDS_GET_METHOD,
                self::API_SVS_GIFTCARDS_SALE_METHOD,
                self::API_SVS_GIFTCARDS_CREATE_METHOD,
            ),
            'devices' => array(
                self::API_SVS_GIFTCARDS_GETNAMESPACE_METHOD,
            )
        );

        foreach($areas as $area => $methods)
        {
            if( array_search($functionName, $methods) !== FALSE )
            {
                return $area;
            }
        }
    }
}
