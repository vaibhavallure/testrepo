<?php
class Teamwork_Common_Model_Chq_Http extends Mage_Core_Model_Abstract
{
    public function request($url, $bodyData=array(), $headers=array(), $config=array('header'=>0) )
    {
        $accessData = array(
            'ApiKey'            => Mage::helper('teamwork_common/adminsettings')->getAccessToken(),
            'Source'            => Mage::helper('teamwork_common/adminsettings')->getSource(),
            'Async'             => true,
        );

        $writeLog = Mage::helper('teamwork_common/adminsettings')->writeLog();
        $bodyData = array_merge($bodyData, $accessData);
        
        if($writeLog)
        {
            Mage::log($bodyData, null, 'chqapi.log');
        }
        
        $body = http_build_query($bodyData);
        
        $http = new Varien_Http_Adapter_Curl();
        if($config)
        {
            $http->setConfig($config);
        }
        
        $http->write(Zend_Http_Client::POST, $url, '1.1', $headers, $body);
        $response = $http->read();
        
        if($writeLog)
        {
            Mage::log($response, null, 'chqapi.log');
        }
        
        try
        {
            $this->checkResponse($http);
            /* echo '<pre>';
                echo htmlentities( $response );
            echo '</pre>'; */
            return $response;
        }
        catch(Exception $e)
        {
            echo '<pre>';
                echo $e->getMessage();
            echo '</pre>';
            //TODO1 Logger
        }
    }
    
    public function checkResponse($http)
    {
        if( $http->getError() )
        {
            throw new Exception($http->getError());
        }
        
        if( $http->getInfo(CURLINFO_HTTP_CODE) != 200 )
        {
            throw new Exception("HTTP Response code {$http->getInfo(CURLINFO_HTTP_CODE)}");
        }
    }
}