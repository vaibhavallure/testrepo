<?php

class Teamwork_Service_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_tempDir = null;
    protected $_url = null;
    protected $_errorlevels = array(1, 4, 16, 64);
    protected $_ignoreCurlErrno = array(28);
    protected $_realtimeavailabilityModulName = 'Teamwork_Realtimeavailability';

    public function fatalErrorObserver()
    {
        if(function_exists('register_shutdown_function'))
        {
            register_shutdown_function(array($this, 'registrateFatalError'));
        }
    }

    public function registrateFatalError()
    {
        if(function_exists('error_get_last'))
        {
            $error = error_get_last();
            if(!empty($error) && in_array($error['type'], $this->_errorlevels))
            {
                Mage::log($error);
            }
        }
    }

    public function makeCurlRequest($id, $key, $timeout, $url=false)
    {
        $http = new Varien_Http_Adapter_Curl();
        $http->setConfig(array('timeout' => $timeout, 'header' => 0));
        $http->write(Zend_Http_Client::POST, $url ? $url : $this->_url, 1.1, array(), array($key => $id));

        $return = $http->read();
        if($http->getError() && !in_array($http->getErrno(), $this->_ignoreCurlErrno))
        {
            Mage::log($http->getErrno() . ": " . $http->getError());
        }
        $http->close();
        return $return;
    }

    public function runStaging($request_id)
    {
        $configObject = Mage::getConfig();
        $this->_url = $configObject->getNode('teamwork_service/staging_url');

        if($configObject->getNode('teamwork_service/is_local_staging_url'))
        {
            $params = array();
            if( Mage::getStoreConfigFlag('web/secure/use_in_adminhtml') )
            {
                array_push($params, array('_secure' => true));
            }
            $this->_url = $this->_url ? Mage::getUrl(trim($this->_url, '\\/'), $params) : null;
        }

        if(!empty($this->_url))
        {
            if($configObject->getNode('teamwork_service/is_async_type'))
            {
                $timeout = 20;
            }
            else
            {
                $timeout = 0;
            }
            $this->makeCurlRequest($request_id, 'request_id', $timeout);
        }
        else
        {
            //todo log url arn't specified
        }
    }

    public function runStatus($ids, $orderManagedInOms = false)
    {
        $configObject = Mage::getConfig();
        $configPath = ($orderManagedInOms) ? 'teamwork_service/statusoms_url' : 'teamwork_service/status_url';
        $this->_url = $configObject->getNode($configPath);

        if($configObject->getNode('teamwork_service/is_local_status_url'))
        {
            $params = array();
            if( Mage::getStoreConfigFlag('web/secure/use_in_adminhtml') )
            {
                array_push($params, array('_secure' => true));
            }
            $this->_url = $this->_url ? Mage::getUrl(trim($this->_url, '\\/'), $params) : null;
        }

        if(!empty($this->_url))
        {
            $return    = array();
            $paramName = ($orderManagedInOms) ? 'status_id' : 'package_id';
            foreach($ids as $id)
            {
                $response = $this->makeCurlRequest($id, $paramName, 0);

                /**
                * @ according to impossibility to exclude response header in Varien_Http_Adapter_Curl till Magento v. 1.7
                */
                $response = explode("\r\n\r\n", $response);
                $return[] = end($response);
            }

            return $return;
        }
        else
        {
            //todo log url arn't specified
        }
    }

    public function getTempDir()
    {
        if(is_null($this->_tempDir))
        {
            $tempDir = (string)Mage::getConfig()->getNode("teamwork_service/temp_dir");
            $tempDir = str_replace(array('\\', '/'), array(DS, DS), trim($tempDir, '\\/'));
            $tempDir = Mage::getBaseDir('base') . DS . $tempDir;
            if (!file_exists($tempDir))
            {
                mkdir($tempDir, 0777, true);
            }
            $this->_tempDir = $tempDir . DS;
        }
        return $this->_tempDir;
    }

    public function useRealtimeavailability()
    {
        return Mage::helper('core')->isModuleEnabled($this->_realtimeavailabilityModulName);
    }

    /**
     * Checks whether given string begins with integer
     *
     * @param  string  $string
     *
     * @return boolean
     */
    public function stringBeginsWithInteger($string)
    {
        return ((string) intval($string[0]) == $string[0]);
    }

    /**
     * Takes md5 of given string and format result as a GUID
     *
     * @param  string $data
     *
     * @return string
     */
    public function getGuidFromString($data, $dataIsHash = false)
    {
        $hash = ($dataIsHash) ? $data : md5($data);

        return '' .
        substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12) .
        '';
    }
    
    public function getMappingAttributeOptions($channel_id)
    {
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('frontend_label', array('notnull' => true))
            ->setOrder('frontend_label','ASC')
            ->addSetInfo()
            ->getData();
        
        //List collection mapping rich media
        $collection = Mage::getModel('teamwork_service/richmedia')->getCollection()
            ->addFieldToFilter('channel_id', $channel_id);
        
        $richmediaArray = array();
        
        foreach ($collection as $value)
        {
            $richmediaArray[$value['attribute_id']] = $value['media_index'];
        }
        
        $options = array();
       
        foreach($attributes as $attribute){
            
            if (!isset($richmediaArray[$attribute['attribute_id']]))
            {
                $options[] = array(
                    'label' => $attribute["frontend_label"],
                    'value' => $attribute["attribute_id"],
                );
            }
        }
        
        return $options;
    }
    
    public function getListChannels()
    {
        $db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT * FROM {$db->getTable('service_channel')}";
        $result = $db->getResults($query);
        
        return $result;
    }
    
    public function getChannelsList()
    {
        $db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT * FROM {$db->getTable('service_channel')}";
        $channels = $db->getResults($query);
        
        $output = array();
        
        foreach($channels as $channel)
        {
            $output[$channel['channel_id']] = $channel['channel_name'];
        }
        return $output;
    }
    
    public function getChannelNameById($channelGuid)
    {
        $db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT * FROM {$db->getTable('service_channel')}";
        return $db->getOne('service_channel', array('channel_id' => $channelGuid), 'channel_name');
    }

    public function getChannelsOptions()
    {
        $db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT * FROM {$db->getTable('service_channel')}";
        $channels = $db->getResults($query);
        
        $options = array();
        $options[] = array(
            'label' => '',
            'value' => ''
        );
        foreach ($channels as $channel) {
            $options[] = array(
                'label' => $channel['channel_name'],
                'value' => $channel['channel_id'],
            );
        }
        return $options;
    }
    
    public function getMappingfieldList()
    {
        $collection = Mage::getModel('teamwork_service/chqmappingfields')->getCollection()->setOrder('label', 'ASC')->load();
        
        $output = array();
        
        foreach($collection as $value)
        {
            $output[$value['entity_id']] = $value['label'];
        }
        return $output;
    }
    
    public function getMappingfieldOptions($type_Id)
    {
        $collection = Mage::getModel('teamwork_service/chqmappingfields')->getCollection()
            ->addFieldToFilter('type_id', $type_Id)
            ->setOrder('label', 'ASC')
            ->load();
        
        $options = array();
        
        foreach($collection as $value)
        {
            if (!empty($value['value']))
            {
                $options[] = array(
                    'label' => $value['label'],
                    'value' => $value['entity_id'],
                );
            }
        }
        return $options;
    }
    
    
    public function getAttributeList()
    {
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('frontend_label', array('notnull' => true))
            ->setOrder('frontend_label','ASC')
            ->addSetInfo()
            ->getData();
        
        $output = array();
        
        foreach($collection as $value)
        {
            $output[$value['attribute_id']] = $value['frontend_label'];
        }
        return $output;
    }
    
    
    public function getAttributeOptions($channelId, $typeId, $attribute_id)
    {
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('frontend_label', array('notnull' => true))
            ->setOrder('frontend_label','ASC')
            ->addSetInfo()
            ->getData();
        
        $chqMapping = Mage::getModel('teamwork_service/mappingproperty')->getCollection()
                    ->addFieldToFilter('channel_id', (string)$channelId)
                    ->addFieldToFilter('type_id', (string)$typeId)
                    ->load();
        
        $mappingArray = array();
        
        foreach ($chqMapping as $value)
        {
            $mappingArray[$value['attribute_id']] = $value['field_id'];
        }
        
        $options = array();
       
        foreach($attributes as $attribute)
        {    
            if (!isset($mappingArray[$attribute['attribute_id']]) || $attribute_id == $attribute['attribute_id'])
            {
                $options[] = array(
                    'label' => $attribute["frontend_label"],
                    'value' => $attribute["attribute_id"],
                );
            }
        }
        
        return $options;
    }
    
    public function getListTemplateElements($channelId)
    {
        $db = Mage::getModel('teamwork_service/adapter_db');
        
        $settingName = Teamwork_Service_Model_RICHMEDIA::SETTINGS_TEMPLATE_ELEMENTS;
        
        $query = "SELECT setting_value FROM {$db->getTable('service_settings')} WHERE channel_id = '$channelId' AND setting_name = '$settingName'";
        $result = $db->getResults($query);
        
        $options = array();
        if (isset($result[0]['setting_value']))
        {
            foreach(unserialize($result[0]['setting_value']) as $element){
                $options[$element["ecIndex"]] = $element["name"];
            }
        }
        return $options;
    }
}