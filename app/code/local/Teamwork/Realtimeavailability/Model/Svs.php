<?php
abstract class Teamwork_Realtimeavailability_Model_Svs extends Mage_Core_Model_Abstract
{
    public $recordLogs = true;
    
    const API_RTA_POSTDOCUMENT_METHOD = '/rta/itemsavailability/postdocument';
    const API_RTA_ITEM_BATCH_METHOD = '/rta/itemsavailability/batchquantity';
    const API_RTA_ALL_CHANGED_BATCH_METHOD = '/rta/itemsavailability/batchupdateditems';
    
    const API_SVS_LOG_FILE = 'rta.log';
    const API_SVS_EXCEPTION_FILE = 'rta_exception.log';
    
    protected $_serverError = 'Internal Server Error. Please contact the site administrator.';
    protected $_rtaCallNumber = 3;
    
    /* protected function register($params)
    {
        $response = $this->request($params, self::API_RTA_POSTDOCUMENT_METHOD);
        return $response;
    } */
    
    protected function partialBatch($params)
    {
        $response = $this->request($params, self::API_RTA_ITEM_BATCH_METHOD);
        return $response;
    }
    
    protected function changedBatch($params)
    {
        $response = $this->request($params, self::API_RTA_ALL_CHANGED_BATCH_METHOD);
        return $response;
    }
    
    protected function request($params, $functionName)
    {
        $header = array(
            'Content-type: application/json'
        );
        
        $params = json_encode($params);
        
        if($this->recordLogs)
        {
            Mage::log($this->getFullPath($functionName), null, self::API_SVS_LOG_FILE);
            Mage::log($params, null, self::API_SVS_LOG_FILE);
        }
        
        $http = new Varien_Http_Adapter_Curl();
        $http->setConfig( array('timeout' => 600, 'header' => 0) );
        
        $http->write(Zend_Http_Client::POST,  $this->getFullPath($functionName), '1.1', $header, $params);
        
        for($call=1; $call<=$this->_rtaCallNumber; $call++)
        {
            $return = $http->read();
            
            if( $http->getInfo(CURLINFO_HTTP_CODE) == '200' )
            {
                if(stripos($return, 'HTTP/1.1') === 0) // bug magento 1.5
                {
                    $response = explode("\r\n\r\n", $return);
                    $return = end($response);
                }
                break;
            }
        }
        
        return $this->errorHandler(json_decode($return,1), $http);
    }
    
    protected function getFullPath($functionName)
    {
        return rtrim(Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_PATH), '/') . $functionName;
		// return Mage::getSingleton('teamwork_realtimeavailability/resource')->getRtaUri(  ) . $functionName;
		//TODO channel_id for getRtaUri()
    }
    
    protected function errorHandler($response, $http)
    {
		if( !empty($response['responseCode']) )
        {
            Mage::log($response, null, self::API_SVS_EXCEPTION_FILE);
        }
        else
        {
            if($this->recordLogs)
            {
                Mage::log($response, null, self::API_SVS_LOG_FILE);
            }
        }
        
        if( $http->getInfo(CURLINFO_HTTP_CODE) != '200' )
        {
            $response = $this->_serverError;
            Mage::log("Teamwork RTA {$http->getInfo(CURLINFO_HTTP_CODE)}:" . $response, null, self::API_SVS_EXCEPTION_FILE);
        }
        
        return $response;
    }
}