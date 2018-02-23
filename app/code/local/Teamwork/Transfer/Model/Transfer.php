<?php
/**
 * ECM processing model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Transfer extends Teamwork_Transfer_Model_Abstract
{
    /**
     * Array of cached Teamwork_Transfer_Model_Class_ ... classes' objects (see "_getClassObject" method)
     *
     * @var array
     */
    protected $_classes = array();

    /**
     * Database connection object
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    /**
     * Variables used for ECM processing
     *
     * @var array
     */
    protected $_globalVars = array();

    /**
     * Time holder to prevent error status setting while long ECM process operations (see checkLastUpdateTime method)
     *
     * @var int
     */
    protected $_lastUpdateTime;

    /**
     * Threshold in minutes to update "last_update" column in "service" table (see checkLastUpdateTime method)
     *
     * @var int
     */
    protected $_minUpdateInterval = 1;

    /*
    globalVars:
     * request_id
     * channel_id
     * store_id
     * websites
     */

    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Entry point
     */
    public function run($request_id)
    {
        $ecm = $this->getEcmForRun($request_id);
        if( $ecm )
        {
            $this->initialize($ecm);
            foreach($ecm['response'] as $type => $value)
            {
                $this->executeBlock($type);
            }
            
            $data = array(
                'status' => Teamwork_Service_Model_Ecm::ECM_STATUS_REINDEX,
                'end' => date('Y-m-d H:i:s'),
            );
            $this->updateServiceTable($data, 'request_id', $this->_globalVars['request_id']);
            
            try
            {
                Mage::helper('teamwork_transfer/cache')->cleanCache($this);
                Mage::helper('teamwork_transfer/reindex')->runIndexer($this, $this->getEcmsInRow());
            }
            catch(Exception $e)
            {
                $this->_getLogger()->addException($e);
            }

            $status = Teamwork_Service_Model_Ecm::ECM_STATUS_DONE;
            if( $this->hasErrorMsgs() )
            {
                $status = Teamwork_Service_Model_Ecm::ECM_STATUS_ERROR;
            }
            
            $data = array(
                'status' => $status,
                'end' => date('Y-m-d H:i:s'),
            );
            $this->updateServiceTable($data, 'request_id', $this->_globalVars['request_id']);
        }

        $this->runNextEcm();
    }

    /**
     * Prepare working objects
     */
    protected function initialize($ecm)
    {
        ini_set('memory_limit', '1024M');
        $this->markStarted($ecm);
        $this->initGlobals($ecm);
        $this->checkLastUpdateTime();
        $this->checkCustomAttributesFromMapping();
    }

    /**
     * start ECM processing
     */
    protected function markStarted($ecm)
    {
        $data = array(
            'start'     => date('Y-m-d H:i:s'),
            'status'    => Teamwork_Service_Model_Ecm::ECM_STATUS_PROCESSING,
        );
        $this->updateServiceTable($data, 'request_id', $ecm['request_id']);
    }

    /**
     * finish ECM processing
     */
    protected function markFinished($ecm)
    {
        $data = array(
            'start'     => date('Y-m-d H:i:s'),
            'end'       => date('Y-m-d H:i:s'),
            'status'    => Teamwork_Service_Model_Ecm::ECM_STATUS_DONE,
            'response'  => $ecm['response'],
        );
        
        foreach($data['response'] as $k => $v)
        {
            $data['response'][$k]['status'] = 'Success';
        }
        $this->updateServiceTable($data, 'request_id', $ecm['request_id']);
    }
    
    /**
     * initialize _globalVars
     */
     
    protected function initGlobals($ecm)
    {
        $this->_globalVars['request_id'] = $ecm['request_id'];
        $this->_globalVars['channel_id'] = $ecm['channel_id'];
        
        
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_channel'), array('channel_name'))
        ->where('channel_id = ?', $ecm['channel_id']);
        $name = $this->_db->fetchOne($select);
        
        foreach (Mage::app()->getWebsites() as $website)
        {
            foreach($website->getGroups() as $group)
            {
                foreach($group->getStores() as $store)
                {
                    if(strtolower(trim($store->getCode())) == strtolower(trim($name)))
                    {
                        $this->_globalVars['store_id'] = $store->getId();
                        $this->_globalVars['websites'] = array($website->getId());
                        break;
                    }
                }
            }
        }
        if( empty($this->_globalVars['store_id']) )
        {
            throw new Exception("Error occured while initialize ECM's store");
        }
        Mage::app()->setCurrentStore($this->_globalVars['store_id']);
    }
    

    /**
     * Wrapper of ECM part of type "type" processor
     *
     * @param string $type
     */
    protected function executeBlock($type)
    {
        switch($type)
        {
            case 'Categories':
                $this->_getClassObject('synchronization')->init($this->_globalVars);
                $this->_getClassObject('synchronization')->execute();

                $this->_getClassObject('category')->init($this->_globalVars);
                $this->_getClassObject('category')->execute();
                
                $this->_addErrorMsgs($this->_getClassObject('category')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('category')->getWarningMsgs());
            break;

            case 'Styles':
                $this->_getClassObject('attribute')->init($this->_globalVars);
                $this->_getClassObject('attribute')->execute();
                
                $this->_getClassObject('item')->init($this->_globalVars);
                $this->_getClassObject('item')->execute();
                
                $this->_addErrorMsgs($this->_getClassObject('item')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('item')->getWarningMsgs());
            break;

            case 'Prices':
                $this->_getClassObject('price')->init($this->_globalVars);
                $this->_getClassObject('price')->execute();
                
                $this->_addErrorMsgs($this->_getClassObject('price')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('price')->getWarningMsgs());
            break;

            case 'Qtys':
                $quantityModel = $this->_getClassObject('quantity');
                if( $quantityModel->mode != $quantityModel::FULL_INVENTORY_RECOUNT_MODE )
                {
                    $quantityModel->init($this->_globalVars);
                    $quantityModel->execute();
                }
                $this->_addErrorMsgs($this->_getClassObject('quantity')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('quantity')->getWarningMsgs());
                $this->updateEcm($type);
            break;

            case 'AttributeSets':
                $this->_getClassObject('attribute')->init($this->_globalVars);
                $this->_getClassObject('attribute')->execute();
                
                $this->_addErrorMsgs($this->_getClassObject('attribute')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('attribute')->getWarningMsgs());
                $this->updateEcm($type);
            break;

            case 'Packages':
                $this->_getClassObject('package')->init($this->_globalVars);
                $this->_getClassObject('package')->execute();
                
                $this->_addErrorMsgs($this->_getClassObject('package')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('package')->getWarningMsgs());
            break;

            case 'Locations':
                if( Mage::helper('teamwork_service')->useRealtimeavailability() )
                {
                    Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability')->changedInventory($this->_globalVars, $this);
                    $this->checkLastUpdateTime();
                }

                $quantityModel = $this->_getClassObject('quantity');
                $quantityModel->mode = $quantityModel::FULL_INVENTORY_RECOUNT_MODE;
                $quantityModel->init($this->_globalVars);
                $quantityModel->execute();

                $this->_addErrorMsgs($this->_getClassObject('quantity')->getErrorMsgs());
                $this->_addWarningMsgs($this->_getClassObject('quantity')->getWarningMsgs());
                $this->updateEcm($type);
            break;
        }
    }

    /**
     * Keep ECM "in process" by updating "last_update" ECM field
     */
    public function checkLastUpdateTime()
    {
        if(empty($this->_lastUpdateTime) || ((time() - $this->_lastUpdateTime)/60) >= $this->_minUpdateInterval)
        {
            $this->_lastUpdateTime = time();
            $data = array(
                'last_update' => date('Y-m-d H:i:s', $this->_lastUpdateTime)
            );
            $this->updateServiceTable($data, 'request_id', $this->_globalVars['request_id']);
        }
    }

    /**
     * Get ECM(s) and initiate processing
     */
    protected function getEcmForRun($request_id)
    {
        $this->shutdownOverdueEcm();
        
        if( !$this->getProcessingEcm() )
        {
            $ecm = $this->getFirstEcmInRow();
            if( $ecm && $ecm['request_id'] == $request_id )
            {
                if (!Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_INVENTORY))
                {
                    $this->markFinished($ecm);
                    return null;
                }
                return $ecm;
            }
        }
    }

    /**
     * Check if ECM is no longer "in process", correct status and return error
     *
     * @param array $record
     *
     * @return bool
     */
    protected function shutdownOverdueEcm()
    {
        $processingEcms = $this->getProcessingEcm();
        if( $processingEcms )
        {
            foreach($processingEcms as $ecm)
            {
                $dataForUpdate = array();
                $ecmDuration = (time() - strtotime($ecm['last_update'])) / 60;
                
                if( $ecm['status'] == Teamwork_Service_Model_Ecm::ECM_STATUS_REINDEX && $ecmDuration >= Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_MAX_REINDEX_INTERVAL) )
                {
                    $status = Teamwork_Service_Model_Ecm::ECM_STATUS_DONE;
                    foreach($ecm['response'] as $key => $val)
                    {
                        if($val['status'] == 'Error')
                        {
                            $status = Teamwork_Service_Model_Ecm::ECM_STATUS_ERROR;
                            break;
                        }
                    }
                    $dataForUpdate = array('status' => $status);
                }
                elseif( $ecm['status'] != Teamwork_Service_Model_Ecm::ECM_STATUS_REINDEX && $ecmDuration >= Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_MAX_ECM_UPDATE_INTERVAL) )
                {
                    $status = Teamwork_Service_Model_Ecm::ECM_STATUS_ERROR;
                    foreach($ecm['response'] as $key => $val)
                    {
                        if($val['status'] == 'Wait')
                        {
                            $ecm['response'][$key]['status'] = 'Error';
                            $ecm['response'][$key]['errors'][] = 'Unknown server interruption';
                        }
                    }
                    $dataForUpdate = array('status' => $status, 'response' => $ecm['response']);
                }
                
                if(!empty($dataForUpdate))
                {
                    $this->updateServiceTable($dataForUpdate, 'request_id', $ecm['request_id']);
                }
            }
        }
    }

    /**
     * Update ECM record in "service" table
     *
     * @param string $type
     */
    protected function updateEcm($type)
    {
        $status = 'Success';
        if( $this->hasErrorMsgs() )
        {
            $status = 'Error';
        }
        
        $ecm = $this->getEcmByRequestId($this->_globalVars['request_id']);
        if( !empty($ecm['response']) && isset($ecm['response'][$type]) )
        {
            $ecm['response'][$type] = array(
                'status'    => $status,
                'errors'    => $this->prepareError( $this->getErrorMsgs() ),
                'warnings'  => $this->prepareError( $this->getWarningMsgs() ),
            );
            $data = array(
                'response' => $ecm['response'],
            );
            $this->updateServiceTable($data, 'request_id', $this->_globalVars['request_id']);
        }
    }

    /**
     * Cut error messages to make save ECM response data saving to "service" table
     *
     * @param array $errors
     *
     * @return $array
     */
    protected function prepareError($messages)
    {
        $return = array();
        if( !empty($messages) )
        {
            $messages = array_unique($messages);
            $allowedSymbolsPerMessage = pow(2,15) / count($messages);
            
            foreach($messages as $message)
            {
                $return[] = substr($message, 0, $allowedSymbolsPerMessage);
            }
            return $return;
        }
        return $return;
    }
    
    /**
     * @return array
     */
    protected function getProcessingEcm()
    {
        $statuses = array(Teamwork_Service_Model_Ecm::ECM_STATUS_PROCESSING, Teamwork_Service_Model_Ecm::ECM_STATUS_REINDEX);
        return $this->getServiceTable('status', $statuses);
    }
    /**
     * @return array
     */
    protected function getEcmsInRow()
    {
        $statuses = array(Teamwork_Service_Model_Ecm::ECM_STATUS_NEW);
        return $this->getServiceTable('status', $statuses);
    }
    
    /**
     * @param string  $requestId
     *
     * @return array
     */
    protected function getEcmByRequestId($requestId)
    { 
        $result = $this->getServiceTable('request_id', $requestId);
        if( !empty($result) )
        {
            return current($result);
        }
    }
    
    /**
     * @param array  $statuses
     *
     * @return array
     */
    protected function getServiceTable($column, $columnValue)
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service'))
            ->where("{$column} IN(?)", $columnValue)
        ->order('entity_id');
        $results = $this->_db->fetchAll($select);
        foreach($results as $key => $result)
        {
            if( isset($result['response']) )
            {
                $results[$key]['response'] = (array) (@unserialize($result['response']));
            }
        }
        return $results;
    }
    
    /**
     * @param array  $statuses
     *
     * @return array
     */
    protected function updateServiceTable($data, $column, $columnValue)
    {
        foreach($data as $key => $value)
        {
            if( $key == 'response' )
            {
                $data[$key] = serialize($value);
            }
        }
        $this->_db->update(Mage::getSingleton('core/resource')->getTableName('service'), $data, "{$column} = '{$columnValue}'");
    }
    
    /**
     * @return bool
     */
    protected function getFirstEcmInRow()
    {
        $newEcms = $this->getEcmsInRow();
        if($newEcms)
        {
            return reset($newEcms);
        }
    }

    /**
     * Check if we have no running but only newly added ECMs and process an oldest one if needed
     *
     * @return bool
     */
    protected function runNextEcm()
    {
        $ecm = $this->getFirstEcmInRow();
        $processingEcms = $this->getProcessingEcm();

        if( $ecm && !$processingEcms )
        {
            Mage::helper('teamwork_service')->runStaging($ecm['request_id']);
        }
    }

    /**
     * Check/create attributes from mapping
     */
    protected function checkCustomAttributesFromMapping()
    {
        $mappingObj = Mage::getModel('teamwork_service/mapping');
        $mappingObj->getMappingFields($this->_globalVars['channel_id']);
        $mapping = array_merge((array)$mappingObj->getMapCustomStyle(), (array)$mappingObj->getMapCustomItem());
        if (!empty($mapping))
        {
            $this->_getClassObject('attribute')->init($this->_globalVars);
            foreach($mapping as $attribute_name => $attribute_field)
            {
                $this->_getClassObject('attribute')->getAttributeData($attribute_name, strtolower($attribute_field));
            }
        }
    }

    /**
     * Get ECM processor
     *
     * @param string $class
     * @param array  $params (for "sku" special attributes contains "class_item_object" => Teamwork_Transfer_Model_Class_Item)
     *
     * @return Teamwork_Transfer_Model_Class_Attribute_{$class}
     */
    protected function _getClassObject($class, $params = NULL)
    {
        if (!isset($this->_classes[$class])) {
            $this->_classes[$class] = Mage::getModel("teamwork_transfer/class_{$class}", is_null($params) ? array() : $params);
        }
        return $this->_classes[$class];
    }
}
