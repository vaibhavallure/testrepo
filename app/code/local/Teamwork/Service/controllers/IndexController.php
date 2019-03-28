<?php
class Teamwork_Service_IndexController extends Mage_Core_Controller_Front_Action
{
    const API_USERNAME_CONFIG_PATH = 'teamwork_service/api_user/username';
    const API_PASSWORD_CONFIG_PATH = 'teamwork_service/api_user/default_key';

    protected $_apiClient = null;
    protected $_apiSession = null;

    public function _construct()
    {
        Mage::helper('teamwork_service')->fatalErrorObserver();
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', '1');
        set_time_limit(0);
        ob_start();
    }
    
    public function ecmAction()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id', false);
        $date = $request->getParam('date', false);
        $api = $request->getParam('api', true);

        if(!empty($id) && !empty($date))
        {
            $dir = Mage::helper('teamwork_service')->getTempDir() . $date . DIRECTORY_SEPARATOR . $id;
            if ($handle = opendir($dir))
            {
                Mage::getSingleton('core/resource')->getConnection('core_write')->delete(Mage::getSingleton('core/resource')->getTableName('service'), array(
                    'request_id = ?'     => $id
                ));

                while (false !== ($file = readdir($handle)))
                {
                    if ($file != "." && $file != ".." && !is_dir($file))
                    {
                        $content = base64_encode(file_get_contents($dir . DIRECTORY_SEPARATOR . $file));
                        if( $api )
                        {
                            $response = $this->_apiCall('getdata', array($content, false));
                        }
                        else
                        {
                            $response = Mage::getSingleton('teamwork_service/api')->getdata($content, false);
                        }
                        echo htmlentities( base64_decode($response) );
                    }
                }
                if( $api )
                {
                    $this->_destroyApiConnectionAction();
                }
                closedir($handle);
            }
        }
    }
    
    public function lostOrdersAction()
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $coloumn = !empty($_GET['status']) ? 'status' : 'state';
        $sql = "SELECT sord.increment_id, sord.state, sord.`status`, sord.created_at FROM
            " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order') . " sord
        LEFT JOIN " . Mage::getSingleton('core/resource')->getTableName('service_weborder') . " web ON web.OrderNo=sord.increment_id
            WHERE sord.`{$coloumn}`='complete' and web.WebOrderId is null and sord.created_at>'2015-06-28'
        order by sord.created_at DESC";
        $result = $db->fetchAll($sql);
        echo '<pre>';
            print_r($result);
        echo '</pre>';
    }

    public function forcestartAction()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id', false);
        if(!empty($id) && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $id))
        {
            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $db->update(Mage::getSingleton('core/resource')->getTableName('service'), array('status' => 'new'), "request_id = '{$id}'");
            Mage::helper('teamwork_service')->runStaging($id);
        }
    }

    public function forcestartstreightAction()
    {
        $request = $this->getRequest();
        $db      = Mage::getSingleton('core/resource')->getConnection('core_write');

        $id = $request->getParam('id', false);

        // if 'last_ecm' param exists in params, we will process last (most recently created) ECM
        if ($processLastId = $request->getParam('last_ecm', false))
        {
            $select = $db->select()
                ->from(Mage::getSingleton('core/resource')->getTableName('service'), array('request_id'))
            ->order('rec_creation DESC');

            $id = $db->fetchOne($select);
        }

        if(!empty($id) && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $id))
        {
            $db->update(Mage::getSingleton('core/resource')->getTableName('service'), array('status' => 'new'), "request_id = '{$id}'");
            try
                {
                    $transferModel = Mage::getModel("teamwork_transfer/transfer");
                    $transferModel->run($id);
                }
                catch (Exception $e) //catch errors not caught by transfer model
                {
                    echo 'unxpected errors:<br/>';
                    print_r($e->getMessage());
                }

                if ($transferModel->hasWarningMsgs()) //print transfer warning messages
                {
                    echo '<br/><br/>warnings:<br/>';
                    print_r($transferModel->getWarningMsgs());
                }

                if ($transferModel->hasErrorMsgs()) //print transfer error messages
                {
                    echo '<br/><br/>errors:<br/>';
                    print_r($transferModel->getErrorMsgs());
                }

                $select = $db->select()
                    ->from(Mage::getSingleton('core/resource')->getTableName('service'), array('status'))
                ->where('request_id = ?', $id);
                $status = $db->fetchOne($select);

                echo "<br/><br/>Ecm status:<span id='ecm-status'>{$status}</span>"; //print ecm status
        }
    }

    public function twAction()
    {
        $request = $this->getRequest();
        $call = $request->getParam('call', false);
        if ($call !== false && array_search($call, get_class_methods('Teamwork_Service_Model_Api')) !== false)
        {
            $xml = $request->getParam('xml', false);
            $second = ($xml !== false ? array(str_replace(' ', '+', $xml)) : array());
            $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type', 'text/xml')
            ->setBody(base64_decode($this->_apiCall($call, $second)));
            $this->_destroyApiConnectionAction();
        }
        /*
             * getorders
             * getsettings
             * getversion

             * setmapping
             * setstatus
         */
    }

    public function ecmsAction()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id', false);

        if($id !== false)
        {
            $this->getResponse()
                 ->clearHeaders()
                 ->setHeader('Content-Type', 'text/xml')
            ->setBody(base64_decode($this->_apiCall('getecmstatus', array(base64_encode('<?xml version="1.0" encoding="UTF-8"?><EcmHeaders><EcmHeader EcmHeaderId="' . $id . '"/></EcmHeaders>')))));
            $this->_destroyApiConnectionAction();
        }
    }

    public function getversionAction()
    {
        header('Content-Type: text/xml');
        $version = '<?xml version="1.0" encoding="UTF-8"?>';
        $version .= '<PluginInformation Name="Service Teamwork Plug-in for Magento" Version="' . Mage::getConfig()->getNode('modules')->children()->Teamwork_Service->version . '"> Description of Plug-in. Plug-in for Magento ' . Mage::getVersion() . ' created by Teamwork Retailer Co. </PluginInformation>';
        $this->getResponse()
             ->clearHeaders()
             ->setHeader('Content-Type', 'text/xml')
             ->setBody($version);
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
                $api    = Mage::getModel('teamwork_service/api');
                $return = call_user_func_array(array($api, $action), $array);
            }
            else 
            {
                $this->_createApiConnectionAction();
                $return = $this->_apiClient->call('call', array($this->_apiSession, "service.{$action}", $array));
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