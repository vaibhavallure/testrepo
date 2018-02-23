<?php
class Teamwork_Common_Model_Chq_Xml_Response_Classification extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    const ALTERNATIVE_DEPARTMENT_CLASSIFICATION_TYPE = 'Alternative';
    const NORMAL_DEPARTMENT_CLASSIFICATION_TYPE = 'Normal';
    public function parse()
    {
        parent::parse();
        $this->_parseClassification();
    }
    
    protected function _parseClassification()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->InvenDepSets) )
        {
            foreach($xmlObject->InvenDepSets->children() as $invenDepSet)
            {
                if( $this->_isDeleted($invenDepSet) )
                {
                    continue;
                }
                
                $invenDepSetGuid = $this->_getElement($invenDepSet, 'InvenDepSetId');
                $invenDepSetEntity = $this->_getEntityTable($invenDepSet);
                
                $invenDepSetEntity->loadByGuid($invenDepSetGuid)
                    ->setData($invenDepSetEntity->getGuidField(), $invenDepSetGuid)
                    ->setCode($this->_getElement($invenDepSet, 'DepSetCode'))
                    ->setLevel1Id($this->_getElement($invenDepSet, 'DepartmentId'))
                    ->setLevel2Id($this->_getElement($invenDepSet, 'ClassificationId'))
                    ->setLevel3Id($this->_getElement($invenDepSet, 'Subclass1Id'))
                    ->setLevel4Id($this->_getElement($invenDepSet, 'Subclass2Id'))
                    ->setDepartmentId($this->_getElement($invenDepSet, 'DepartmentId'))
                    ->setClassId($this->_getElement($invenDepSet, 'ClassificationId'))
                    ->setSubclass1Id($this->_getElement($invenDepSet, 'Subclass1Id'))
                    ->setSubclass2Id($this->_getElement($invenDepSet, 'Subclass2Id'))
                ->save();
            }
        }
    }
    
    protected function _getEntityTable($invenDepSet)
    {
        $type = $this->_getElement($invenDepSet, 'ClassificationType');
        switch($type)
        {
            case Teamwork_Common_Model_Chq_Xml_Response_Classification::ALTERNATIVE_DEPARTMENT_CLASSIFICATION_TYPE:
            {
                return Mage::getModel('teamwork_common/staging_acss');
            }
            case Teamwork_Common_Model_Chq_Xml_Response_Classification::NORMAL_DEPARTMENT_CLASSIFICATION_TYPE:
            {
                return Mage::getModel('teamwork_common/staging_dcss');
            }
        }
    }
}