<?php
class Teamwork_Weborder_IndexController extends Mage_Core_Controller_Front_Action
{
    const API_USERNAME_CONFIG_PATH = 'teamwork_weborder/api_user/username';
    const API_PASSWORD_CONFIG_PATH = 'teamwork_weborder/api_user/default_key';

    protected $_apiClient = null;
    protected $_apiSession = null;

    public function _construct()
    {
        Mage::helper('teamwork_weborder')->fatalErrorObserver();
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', '1');
        set_time_limit(0);
        ob_start();
    }

	public function getorderxmlAction()
    {
        $request = $this->getRequest();
        $orderId = $request->getParam('orderid', false);
        $date = $request->getParam('date', null);
        $xml = '';
        if($orderId)
        {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $xml = Mage::getModel('teamwork_weborder/weborder')->generateXml($date, $order);
        }
        else
        {
            $xml = Mage::getModel('teamwork_weborder/weborder')->generateXml($date);
        }
        if( !empty($xml) )
        {
            header("Content-type: text/xml");
            echo base64_decode($xml);
        }
        exit();
    }
    
    public function woapicallAction()
    {
        $response = Mage::getModel('teamwork_weborder/weborder')->generateXml( array() );
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'text/xml')
        ->setBody(base64_decode($response));
    }
    
    public function apicallAction()
    {
        $request = $this->getRequest();
        $method = $request->getParam('method', false);
        
        if($method !== false )
        {
            $params = $request->getParam('params', array());
            if( !empty($params) )
            {
                $params = array(str_replace(' ', '+', $params));
            }
            
            $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type', 'text/xml')
            ->setBody(base64_decode($this->_apiCall($method, $params)));
            $this->_destroyApiConnectionAction();
        }
    }

    protected function _createApiConnectionAction()
    {
        if(empty($this->_apiClient))
        {
            $httpClient = new Zend_Http_Client;
            $httpClient->setConfig(array('timeout' => 6000));

            $this->_apiClient = new Zend_XmlRpc_Client(Mage::getUrl('api/xmlrpc'), $httpClient);

            $configObject = Mage::getConfig();
            $apiUser = (string)$configObject->getNode(self::API_USERNAME_CONFIG_PATH);
            $apiPassword = (string)$configObject->getNode(self::API_PASSWORD_CONFIG_PATH);

            $this->_apiSession = $this->_apiClient->call('login', array($apiUser, $apiPassword));
        }
    }

    protected function _apiCall($action, $array=array())
    {
        $return = '';
        try
        {
            if (isset($_GET['straight']))
            {
                $api    = Mage::getModel('teamwork_weborder/api');
                $return = call_user_func_array(array($api, $action), $array);
            }
            else 
            {
                $this->_createApiConnectionAction();
                $return = $this->_apiClient->call('call', array($this->_apiSession, "teamwork_weborder.{$action}", $array));
            }

        }
        catch(Exception $e)
        {
            Mage::log($e->getMessage());
            Mage::log($e->getTraceAsString());
        }
        return $return;
    }

    protected function _destroyApiConnectionAction()
    {
        if (!empty($this->_apiClient) && !empty($this->_apiSession))
        {
            $this->_apiClient->call('endSession', array($this->_apiSession));
        }
    }
}