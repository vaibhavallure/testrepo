<?php

class Teamwork_Service_Model_Resource_Dam extends Mage_Core_Model_Mysql4_Abstract
{
    const API_NAME  = 'ecommerce-api';
    const DEFAULT_AREA_NAME = 'media';

    protected $_batchLimit = 10;
    protected $_max_request_attemt = 3;

    public function _construct()
    {
    }

    public function requestProductDAMData($styleId, $styleNo)
    {
        if (!$styleId && !$styleNo) return false;
        $data = array();
        if ($styleId) $data['styleID'] = $styleId;
        if ($styleNo) $data['styleNo'] = $styleNo;
        $result = $this->_rawDAMRequest('get-style', $data);
        if ($result['res'] && array_key_exists('style', $result['res'])) return $result['res']['style'];
        return false;

    }

    public function requestBatchDAMData($modifiedAfterTime=false, $callback = false, $portionSize = 10)
    {
        if ($callback && !$portionSize) $portionSize = 1;

        $data = array('limit' => $this->_batchLimit);
        
        Mage::app()->getStore()->resetConfig();

        if (($modifiedAfterTime === false || is_null($modifiedAfterTime)) && $modifiedAfterTime = Mage::getStoreConfig(Teamwork_Service_Helper_Config::XML_PATH_DAM_LAST_UPDATE_TIME))
        {
            $data['modifiedAfter'] = floatval($modifiedAfterTime);
        }
        else
        {
            $modifiedAfterTime = floatval($modifiedAfterTime);
            if ($modifiedAfterTime)
            {
                $data['modifiedAfter'] = $modifiedAfterTime;
            }
        }
        $modifiedAfterTime = false;
        $count = 0;
        $result = array();
        do
        {
            $r = $this->_rawDAMRequest('batch-updated-styles', $data);
            if (!$r['res']) return false;
            $result = array_merge($result, $r['res']['styles']);
            $data['cursor'] = $r['res']['cursor'];
            if (!$modifiedAfterTime)
            {
                $modifiedAfterTime = floatval($r['res']['lastUpdateTime']);
            }

            if ($callback)
            {
                $count += 1;
                if (!$r['res']['cursor'] || $count >= $portionSize)
                {
                    if (!call_user_func($callback, $result, $modifiedAfterTime)) return false;
                    $result = array();
                    $count = 0;
                }
            }

        } while ($r['res']['cursor']);

        Mage::getSingleton('core/config')->saveConfig(Teamwork_Service_Helper_Config::XML_PATH_DAM_LAST_UPDATE_TIME, $modifiedAfterTime);

        return $callback ? $modifiedAfterTime : $result;
    }


    protected function _rawDAMRequest($method, $params=array(), $areaName=self::DEFAULT_AREA_NAME)
    {
        $result = array('res' => false, 'error_msgs' => array());
        $apiKey = Mage::getStoreConfig(Teamwork_Service_Helper_Config::XML_PATH_DAM_API_KEY);
        if (!$apiKey)
        {
            //log error "DAM key is not set"
            $msg = "DAM ERROR: DAM key is not set";
            Mage::helper("teamwork_service/log")->addMessage($msg);
            $result['error_msgs'][] = $msg;
            return $result;
        }
        $rawRes = $this->_getFullDAMUrl($method, $areaName);
        if (!$rawRes['res'])
        {
            return $rawRes;
        }
        $url = $rawRes['res'];

        $header = array(
            'Content-type: application/json',
            'Access-Token: ' . $apiKey
        );

        $params = json_encode($params);


        $http = new Varien_Http_Adapter_Curl();

        $counter = 0;
        do
        {
            $http->setConfig(array('header' => 0));
            $http->write(Zend_Http_Client::POST,  $url, '1.1', $header, $params);
            $rawResponse = $http->read();
            if (Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_DAM_DEBUG_TO_LOG))
            {
                Mage::helper("teamwork_service/log")->addMessage("DAM DEBUG: request: " . $params . " response: " . $rawResponse);
            }

            if ($http->getError() && $http->getError() != CURLE_OK) Mage::helper("teamwork_service/log")->addMessage("DAM WARNING: error occured while DAM data requesting on attempt " . $counter . ": request: " . $params . " response: " . $rawResponse);
            else break;

            $counter++;
        }
        while ($counter < $this->_max_request_attemt);

        if ($counter >= $this->_max_request_attemt)
        {
            $msg = "DAM ERROR: failed to get DAM data: url: {$url}; request: {$params}; last responce: {$rawResponse}";
            Mage::helper("teamwork_service/log")->addMessage($msg);
            $result['error_msgs'][] = $msg;
            return $result;
        }


        if(stripos($rawResponse, 'HTTP/1.1') == 0) // bug magento 1.5
        {
            $rawResponse = explode("\r\n\r\n", $rawResponse);
            $rawResponse = end($rawResponse);
        }


        $return = (array)json_decode($rawResponse, 1);
        if (array_key_exists('errorCode', $return))
        {
            $msg = "DAM ERROR: DAM returned error: request: " . $params . " response: " . $rawResponse;
            Mage::helper("teamwork_service/log")->addMessage($msg);
            if (array_key_exists('errorMessage', $return)) $msg = "DAM message: " . $return['errorMessage'];
            $result['error_msgs'][] = $msg;
            return $result;
        }
        $result['res'] = $return;
        return $result;
    }

    protected function _getFullDAMUrl($functionName, $areaName=self::DEFAULT_AREA_NAME)
    {
        $result = array('res' => false, 'error_msgs' => array());
        $url = Mage::getStoreConfig(Teamwork_Service_Helper_Config::XML_PATH_DAM_URL);
        if (!$url)
        {
            //log error "DAM url is not set"
            $msg = "DAM ERROR: DAM url is not set";
            Mage::helper("teamwork_service/log")->addMessage($msg);
            $result['error_msgs'][] = $msg;
            return false;
        }
        else
        {
            $result['res'] = rtrim($url, "//") . '/' . self::API_NAME . '/' . $areaName . '/' . trim($functionName, "//");
        }
        return $result;
    }

    public function subscribeScheduler()
    {
        return $this->_rawDAMRequest('subscribe', array('url' => $this->_getSubscrUrl()), 'scheduler');
    }

    public function unsubscribeScheduler()
    {
        return $this->_rawDAMRequest('unsubscribe', array('url' => $this->_getSubscrUrl()), 'scheduler');
    }
    
    public function getNamespace()
    {
        $namespace = $this->_rawDAMRequest('getnamespace');
        return !empty($namespace['res']) ? $namespace['res'] : null ;
    }

    protected function _getSubscrUrl()
    {
        $stores = array_keys(Mage::app()->getStores());
        return Mage::getUrl(Teamwork_Service_Helper_Config::DAM_UPDATE_ACTION, array('_secure' => true, '_store' => reset($stores), '_nosid' => true));
    }

}
