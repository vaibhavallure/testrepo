<?php
class Teamwork_Common_Model_Chq_Xml_Request
{
    public $chqStaging;
    protected $_loader;
    const REQUEST_BASE = 'teamwork_common/chq_xml_request_';
    
    public function setChqStaging(Varien_Object $chqStaging)
    {
        $this->chqStaging = $chqStaging;
        $this->_loader = $this->getClassLoader();
    }
    
    public function getRegisterData()
    {
        $this->_loader->generateRequestHeader();
        $this->_loader->generateRequestBody();
        
        return array(
            'Data'              => $this->_loader->getGeneratedXml(),
            'ApiRequestType'    => $this->chqStaging->getApiType(),
            'UseApiVersion2'    => Teamwork_Common_Model_Chq_Api_Type::isImplementedSecondVersion($this->chqStaging->getApiType()),
        );
    }
    
    public function getStatusData()
    {
        return array(
            'Id'    => $this->chqStaging->getDocumentId(),
        );
    }
    
    public function getClassLoader()
    {
        $modelClass = self::REQUEST_BASE . Teamwork_Common_Model_Chq_Api_Type::getClassByType($this->chqStaging->getApiType());
        $defaultModelClass = self::REQUEST_BASE . Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ABSTRACT_CLASS;
        
        if( mageFindClassFile(Mage::getConfig()->getModelClassName($modelClass)) )
        {
            return Mage::getModel( $modelClass, $this->chqStaging );
        }
        else
        {
            return Mage::getModel( $defaultModelClass, $this->chqStaging );
        }
    }
}