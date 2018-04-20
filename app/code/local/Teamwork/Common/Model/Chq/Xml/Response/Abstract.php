<?php
class Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public $chqStaging, $workingDocument;
    public function __construct(array $arguments)
    {
        $this->chqStaging = $arguments['chqStaging'];
        $this->workingDocument = $arguments['workingDocument'];
    }
    
    public function parse()
    {
        
    }
    
    protected function _getElement(SimpleXMLElement $node, $name, $isBool=false)
    {
        
        if(!$isBool)
        {
            $string = Mage::helper('teamwork_common/parser')->getXmlElementString($node,$name);
        }
        else
        {
            $string = Mage::helper('teamwork_common/parser')->getXmlElementBool($node,$name);
        }
        if( Mage::helper('teamwork_common/guid')->isGuidString($string,false) )
        {
            $string = strtolower($string);
        }
        return $string;
    }
    
    protected function _getAttribute(SimpleXMLElement $node, $name, $isBool=false)
    {
        if(!$isBool)
        {
            $string = Mage::helper('teamwork_common/parser')->getXmlElementString($node,null,$name);
        }
        else
        {
            $string = Mage::helper('teamwork_common/parser')->getXmlElementBool($node,null,$name);
        }
        
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
        Mage::helper('teamwork_common')->registrateEcm($channelId, $requestId, $type, $status, $noSalt);
    }
}