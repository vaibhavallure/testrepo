<?php
class Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public $chqStaging;
    public function __construct(Varien_Object $chqStaging)
    {
        $this->chqStaging = $chqStaging;
    }
    
    public function parse()
    {
        
    }
    
    protected function _getElement(SimpleXMLElement $node, $name)
    {
        $string = Mage::helper('teamwork_common/parser')->getXmlElementString($node,$name);
        if( Mage::helper('teamwork_common/guid')->isGuidString($string,false) )
        {
            $string = strtolower($string);
        }
        return $string;
    }
    
    protected function _getAttribute(SimpleXMLElement $node, $name)
    {
        $string = Mage::helper('teamwork_common/parser')->getXmlElementString($node,null,$name);
        if( Mage::helper('teamwork_common/guid')->isGuidString($string,false) )
        {
            $string = strtolower($string);
        }
        return $string;
    }
    
    protected function _isDeleted(SimpleXMLElement $node)
    {
        return $this->_getAttribute($node, 'deleted');
    }
    
    protected function getStoreForProcessing()
    {
        $stores = array();
        foreach(Mage::app()->getStores() as $store)
        {
            $stores[$store->getId()] = $store->getCode();
        }
        return $stores;
    }
    
    protected function _prepareDBDatetime($string)
    {
        if( !empty($string) )
        {
            $datetime = new DateTime($string);
            
            $formatedDatetime = $datetime->format('Y-m-d H:i:s');
            if($formatedDatetime)
            {
                return new Zend_Db_Expr( "'{$formatedDatetime}'" );
            }
        }
    }
    
    protected function _registrateEcm($channelId, $requestId, $type, $status, $noSalt = false)
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