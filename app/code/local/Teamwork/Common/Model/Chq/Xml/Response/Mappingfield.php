<?php
class Teamwork_Common_Model_Chq_Xml_Response_Mappingfield extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseMappingFields();
    }
    
    protected function _parseMappingFields()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->ECommerceFields) )
        {
            foreach($xmlObject->ECommerceFields->children() as $eCommerceField)
            {
                $eCommerceFieldGuid = $this->_getElement($eCommerceField, 'EcmName');
                $eCommerceFieldEntity = Mage::getModel('teamwork_common/staging_mappingfield')->loadByGuid($eCommerceFieldGuid);
                
                if( $this->_isDeleted($eCommerceField) )
                {
                    $eCommerceFieldEntity->delete();
                    continue;
                }
                
                $eCommerceFieldEntity->setData($eCommerceFieldEntity->getGuidField(), $eCommerceFieldGuid)
                    ->setLabel($this->_getElement($eCommerceField, 'FieldName'))
                    ->setType($this->_getElement($eCommerceField, 'FieldType'))
                    ->setTypeId($this->_getElement($eCommerceField, 'FieldSource'))
                ->save();
            }
        }
    }
}