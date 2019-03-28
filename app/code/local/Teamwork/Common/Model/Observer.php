<?php
class Teamwork_Common_Model_Observer extends Mage_Core_Model_Abstract
{
    public function generateProductEcm($observer=null)
    {
        Mage::getModel('teamwork_common/chq')->generateEcm(Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENTORY_EXPORT);
        
        Mage::helper('teamwork_service')->runStaging(true);
    }
    
    public function cleanChqStaging($observer=null)
    {
        Mage::getModel('teamwork_common/staging_chq')->сleanUpChqDocuments();
    }
}