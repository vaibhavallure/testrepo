<?php
class Teamwork_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function registrateEcm($channelId, $requestId, $type, $status, $noSalt = false)
    {
        $saltedGuid = $requestId;
        if(!$noSalt)
        {
            $saltedGuid = Mage::helper('teamwork_common/staging_abstract')->getSaltedRequestId( $requestId, $channelId );
        }
        
        $serviceEntity = Mage::getModel('teamwork_common/staging_service')->loadByChannelAndGuid($channelId, $saltedGuid);
        if( !$serviceEntity->getData($serviceEntity->getGuidField()) )
        {
            $serviceEntity->setRecCreation(Varien_Date::now());
        }
        
        $serviceEntity->setData($serviceEntity->getGuidField(), $requestId)
            ->setChannelId($channelId)
            ->setStatus($status)
            ->setChunk(1)
            ->setNoSalt($noSalt)
            ->setTotalChunks(1)
            ->setResponse( serialize(
                array(
                    $type => array(
                        'status'    => 'Wait',
                        'errors'    => array(),
                        'warnings'  => array(),
                    )
                )
            ))
        ->save();
    }
}