<?php
class Teamwork_Common_Model_Chq_Xml_Response_Class extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    const CLASS_CLASSIFICATION_LEVEL = 'Class';
    const SUBCLASS1_CLASSIFICATION_LEVEL = 'Subclass1';
    const SUBCLASS2_CLASSIFICATION_LEVEL = 'Subclass2';
    
    public function parse()
    {
        parent::parse();
        $this->_parseClass();
    }
    
    protected function _parseClass()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->InvenClasses) )
        {
            foreach($xmlObject->InvenClasses->children() as $invenClass)
            {
                if( $this->_isDeleted($invenClass) )
                {
                    continue;
                }
                
                $invenClassGuid = $this->_getElement($invenClass, 'InvenClassId');
                $invenClassEntity = $this->_getEntityTable($invenClass);
                
                $invenClassEntity->loadByGuid($invenClassGuid)
                    ->setData($invenClassEntity->getGuidField(), $invenClassGuid)
                    ->setCode($this->_getElement($invenClass, 'Code'))
                    ->setName($this->_getElement($invenClass, 'Name'))
                ->save();
            }
        }
    }
    
    protected function _getEntityTable($invenClass)
    {
        $type = $this->_getElement($invenClass, 'ClassificationType');
        $classLevel = $this->_getElement($invenClass, 'Level');
        
        switch($type)
        {
            case Teamwork_Common_Model_Chq_Xml_Response_Classification::ALTERNATIVE_DEPARTMENT_CLASSIFICATION_TYPE:
            {
                switch($classLevel)
                {
                    case self::CLASS_CLASSIFICATION_LEVEL:
                    {
                        return Mage::getModel('teamwork_common/staging_acsslevel2');
                    }
                    case self::SUBCLASS1_CLASSIFICATION_LEVEL:
                    {
                        return Mage::getModel('teamwork_common/staging_acsslevel3');
                    }
                    case self::SUBCLASS2_CLASSIFICATION_LEVEL:
                    {
                        return Mage::getModel('teamwork_common/staging_acsslevel4');
                    }
                }
            }
            case Teamwork_Common_Model_Chq_Xml_Response_Classification::NORMAL_DEPARTMENT_CLASSIFICATION_TYPE:
            {
                switch($classLevel)
                {
                    case self::CLASS_CLASSIFICATION_LEVEL:
                    {
                        return Mage::getModel('teamwork_common/staging_dcssclass');
                    }
                    case self::SUBCLASS1_CLASSIFICATION_LEVEL:
                    {
                        return Mage::getModel('teamwork_common/staging_dcsssubclass1');
                    }
                    case self::SUBCLASS2_CLASSIFICATION_LEVEL:
                    {
                        return Mage::getModel('teamwork_common/staging_dcsssubclass2');
                    }
                }
            }
        }
    }
}