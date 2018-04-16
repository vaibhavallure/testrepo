<?php
class Teamwork_Common_Model_Chq_Xml_Response_Department extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseDepartment();
    }
    
    protected function _parseDepartment()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->InvenDepartments) )
        {
            foreach($xmlObject->InvenDepartments->children() as $invenDepartment)
            {
                if( $this->_isDeleted($invenDepartment) )
                {
                    continue;
                }
                
                $invenDepartmentGuid = $this->_getElement($invenDepartment, 'InvenDepartmentId');
                $invenDepartmentEntity = $this->_getEntityTable($invenDepartment);
                
                $invenDepartmentEntity->loadByGuid($invenDepartmentGuid)
                    ->setData($invenDepartmentEntity->getGuidField(), $invenDepartmentGuid)
                    ->setCode($this->_getElement($invenDepartment, 'Code'))
                    ->setName($this->_getElement($invenDepartment, 'Name'))
                ->save();
            }
        }
    }
    
    protected function _getEntityTable($invenDepartment)
    {
        $type = $this->_getElement($invenDepartment, 'ClassificationType');
        switch($type)
        {
            case Teamwork_Common_Model_Chq_Xml_Response_Classification::ALTERNATIVE_DEPARTMENT_CLASSIFICATION_TYPE:
            {
                return Mage::getModel('teamwork_common/staging_acsslevel1');
            }
            case Teamwork_Common_Model_Chq_Xml_Response_Classification::NORMAL_DEPARTMENT_CLASSIFICATION_TYPE:
            {
                return Mage::getModel('teamwork_common/staging_dcssdepartment');
            }
        }
    }
}