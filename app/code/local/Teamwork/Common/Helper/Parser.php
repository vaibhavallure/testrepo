<?php
class Teamwork_Common_Helper_Parser extends Mage_Core_Helper_Abstract
{
    public function getXmlElementString(SimpleXMLElement $node, $element=null, $attribute=null)
    {
        $allAttributes = $node->attributes('xsi', true);
        if( !(isset($allAttributes['nil']) && $allAttributes['nil'] == 'true') )
        {
            if( !empty($element) )
            {
                if($node->{$element} == 'false')
                {
                    return false;
                }
                if($node->{$element} == 'true')
                {
                    return true;
                }
                return (string) $node->{$element}; //TODO remove
            }
            
            if( !empty($attribute) && isset($node[$attribute]) )
            {
                if($node[$attribute] == 'false')
                {
                    return false;
                }
                if($node[$attribute] == 'true')
                {
                    return true;
                }
                return (string) $node[$attribute]; //TODO remove
            }
        }
    }
    
    public function getXmlElementBool(SimpleXMLElement $node, $element=null, $attribute=null)
    {
        $allAttributes = $node->attributes('xsi', true);
        if( !(isset($allAttributes['nil']) && $allAttributes['nil'] == 'true') )
        {
            if( !empty($element) )
            {
                if($node->{$element} == 'false')
                {
                    return false;
                }
                if($node->{$element} == 'true')
                {
                    return true;
                }
            }
            
            if( !empty($attribute) && isset($node[$attribute]) )
            {
                if($node[$attribute] == 'false')
                {
                    return false;
                }
                if($node[$attribute] == 'true')
                {
                    return true;
                }
            }
        }
    }
    
    public function deserializeXml($xml)
    {
        libxml_use_internal_errors(true);
        $xmlObject = @simplexml_load_string($xml);
        libxml_use_internal_errors(false);
        return $xmlObject;
    }
}