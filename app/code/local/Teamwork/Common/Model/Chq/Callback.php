<?php
class Teamwork_Common_Model_Chq_Callback extends Mage_Core_Model_Abstract
{
    public function createProductEcm($registerDocument)
    {
        foreach(Mage::getModel('teamwork_common/staging_channel')->getChannels() as $channelId => $channel )
        {
            $filter = new Varien_Object();
            $filter->setRequestId(
                Mage::helper('teamwork_common/staging_abstract')->getSaltedRequestId($registerDocument->getDocumentId(), $channelId)
            );
            
            $styleCollection = Mage::getModel('teamwork_common/staging_style')->loadCollectionByVarienFilter($filter);
            if( count($styleCollection) )
            {
                Mage::helper('teamwork_common')->registrateEcm(
                    $channelId,
                    $registerDocument->getDocumentId(),
                    Teamwork_Common_Model_Staging_Service::PROCESSABLE_TYPE_STYLES,
                    Teamwork_Common_Model_Staging_Service::STATUS_NEW
                );
            }
        }
    }
}