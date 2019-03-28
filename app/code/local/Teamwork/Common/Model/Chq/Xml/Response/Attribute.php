<?php
class Teamwork_Common_Model_Chq_Xml_Response_Attribute extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseAttributeSet();
    }
    
    protected function _parseAttributeSet()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->AttributeSets) )
        {
            foreach($xmlObject->AttributeSets->children() as $attributeSet)
            {  
                $attributeGuid = $this->_getElement($attributeSet, 'AttributeSetID');
                $attributeSetEntity = Mage::getModel('teamwork_common/staging_attributeset')->loadByGuid($attributeGuid);
                
                if( $this->_isDeleted($attributeSet) )
                {
                    continue;
                }
                
                $attributeSetEntity->setAttributeSetId($attributeGuid)
                    ->setRequestId( $this->chqStaging->getData('ApiDocumentId') )
                    ->setCode( Mage::helper('teamwork_common/staging_attributeset')->getSafeAttributeCode($this->_getElement($attributeSet, 'Code')) )
                    ->setDescription($this->_getElement($attributeSet, 'Description'))
                    ->setAlias($this->_getElement($attributeSet, 'Alias'))
                ->save();
                
                $this->_parseAttributeValue($attributeSet);
            }
            
            $this->_channels = Mage::getModel('teamwork_common/staging_channel')->getChannels();
            if(!empty($this->_channels))
            {
                foreach($this->_channels as $channelId => $channel)
                {
                    $this->_registrateEcm(
                        $channelId,
                        $this->chqStaging->getData('ApiDocumentId'),
                        Teamwork_Common_Model_Staging_Service::PROCESSABLE_TYPE_ATTRIBUTESETS,
                        Teamwork_Common_Model_Staging_Service::STATUS_NEW,
                        true
                    );
                    break;
                }
            }
        }
    }
    
    public function _parseAttributeValue($attributeSet)
    {
        if( !empty($attributeSet->AttributeSetValues) )
        {
            foreach($attributeSet->AttributeSetValues->children() as $attributeSetValue)
            {
                $attributeValueGuid = $this->_getElement($attributeSetValue, 'AttributeSetValueID');
                $attributeValueEntity = Mage::getModel('teamwork_common/staging_attributevalue')->loadByGuid($attributeValueGuid);
                
                if( $this->_isDeleted($attributeSetValue) )
                {
                    continue;
                }
                
                $attributeValueEntity->setAttributeValueId($attributeValueGuid)
                    ->setAttributeSetId($this->_getElement($attributeSet, 'AttributeSetID'))
                    ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                    ->setAttributeValue($this->_getElement($attributeSetValue, 'Value'))
                    ->setAttributeAlias($this->_getElement($attributeSetValue, 'Alias'))
                    ->setAttributeAlias2($this->_getElement($attributeSetValue, 'Alias2'))
                    ->setOrder($this->_getElement($attributeSetValue, 'Order'))
                ->save();
            }
        }
    }
}