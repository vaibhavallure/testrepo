<?php
class Teamwork_Common_Helper_Staging_Product extends Mage_Core_Helper_Abstract
{
    protected $_processedTypes = array(
        Teamwork_Common_Model_Chq_Xml_Response_Product::PRODUCT_ECTYPE_ECSUSPENDED,
        Teamwork_Common_Model_Chq_Xml_Response_Product::PRODUCT_ECTYPE_ECOFFER,
        Teamwork_Common_Model_Chq_Xml_Response_Product::PRODUCT_ECTYPE_ECDISCONTINUED,
    );
    
    public function convertEcType($type)
    {
        switch($type)
        {
            case Teamwork_Common_Model_Chq_Xml_Response_Product::PRODUCT_ECTYPE_ECSUSPENDED:
            {
                return Teamwork_Common_Model_Chq_Xml_Response_Product::DB_ECTYPE_ECSUSPENDED;
            }
            case Teamwork_Common_Model_Chq_Xml_Response_Product::PRODUCT_ECTYPE_ECOFFER:
            {
                return Teamwork_Common_Model_Chq_Xml_Response_Product::DB_ECTYPE_ECOFFER;
            }
            case Teamwork_Common_Model_Chq_Xml_Response_Product::PRODUCT_ECTYPE_ECDISCONTINUED:
            {
                return Teamwork_Common_Model_Chq_Xml_Response_Product::DB_ECTYPE_ECDISCONTINUED;
            }
			default:
			{
				return $type;
			}
        }
    }
    
    public function getProcessedEcTypes()
    {
        return $this->_processedTypes;
    }
    
    protected function _beforeSave()
    {
        $currentTime = Varien_Date::now();
        if( !$this->getEntityId() && !$this->getCreatedAt() )
        {
            $this->setDateInserted($currentTime);
        }
        $this->setDateUpdated($currentTime);
        
        return parent::_beforeSave();
    }
}