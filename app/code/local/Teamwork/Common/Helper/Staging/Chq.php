<?php
class Teamwork_Common_Helper_Staging_Chq extends Mage_Core_Helper_Abstract
{
    protected $_waitStatuses = array(
        Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_INQUEUE,
        Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_VALIDATION,
        Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_INPROCESS,
        Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_PENDING,
    );
    protected $_successfulStatuses = array(
        Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_SUCCESSFUL,
    );
	protected $_errorStatuses = array(
        Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_ERROR,
    );
    protected $_skipProcess = array();
    
    public function __construct()
    {
        $this->_skipProcess = array(
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_CATEGORY_EXPORT => array(
                !Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_INVENTORY),
                !Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_CATEGORIES),
            ),
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENTORY_EXPORT => array(
                !Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_INVENTORY),
                !Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_PRODUCTS),
            ),
        );
    }
    
    public function getWaitStatuses()
    {
        return $this->_waitStatuses;
    }
    
    public function getSuccessfulStatuses()
    {
        return $this->_successfulStatuses;
    }
    
    public function isWaitStatus($status)
    {
        return in_array($status,$this->_waitStatuses);
    }
    
    public function isSuccessfulStatus($status)
    {
        return in_array($status,$this->_successfulStatuses);
    }

	public function isErrorStatus($status)
    {
        return in_array($status,$this->_errorStatuses);
    }

	public function allowProcess($type)
    {
        $allow = true;
        if( isset($this->_skipProcess[$type]) )
        {
            foreach($this->_skipProcess[$type] as $parameter)
            {
                if($parameter)
                {
                    $allow = false;
                    break;
                }
            }
        }
        return $allow;
    }
}