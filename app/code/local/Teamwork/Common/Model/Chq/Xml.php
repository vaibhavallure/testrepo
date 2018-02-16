<?php
class Teamwork_Common_Model_Chq_Xml implements Teamwork_Common_Model_Chq_ProcessorInterface
{
    public static $rootElement = 'ApiDocument';
    protected $_deserializedObject, $_requestModel, $_responseModel;
    
    public function __construct()
    {
        $this->_requestModel = Mage::getModel('teamwork_common/chq_xml_request');
        $this->_responseModel = Mage::getModel('teamwork_common/chq_xml_response');
    }
    
    public function getFormatedDataForRegisterApi(Varien_Object $chqStaging)
    {
        $this->_requestModel->setChqStaging($chqStaging);
        return $this->_requestModel->getRegisterData();
    }
    
    public function getFormatedDataForStatusApi(Varien_Object $chqStaging)
    {
        $this->_requestModel->setChqStaging($chqStaging);
        return $this->_requestModel->getStatusData();
    }
    
    public function deserialize($string)
    {
        $this->_deserializedObject = new Varien_Object();
        
        $xmlObject = Mage::helper('teamwork_common/parser')->deserializeXml($string);
        if($xmlObject)
        {
            foreach( $xmlObject->attributes() as $attributeKey => $attributeValue )
            {
                $this->_deserializedObject->setData($attributeKey, (string)$attributeValue);
            }
            if(isset($xmlObject->Response))
            {
                $this->_deserializedObject->setResponse($xmlObject->Response);
            }
        }
    }
    
    public function convertDocumentIntoEcm($document)
    {
        if( !empty($document) )
        {
            $this->_deserializedObject->addData((array)$document->getData());
        }
        
        $this->_responseModel->setChqStaging($this->_deserializedObject);
        $this->_responseModel->parse();
    }
    
    public function continueCallChain()
    { 
        return $this->_requestModel->isChainedType();
    }
    
    public function getData($key)
    {
        return $this->_deserializedObject->getData($key);
    }
}