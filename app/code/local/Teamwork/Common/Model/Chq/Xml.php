<?php
class Teamwork_Common_Model_Chq_Xml implements Teamwork_Common_Model_Chq_ProcessorInterface
{
    public static $rootElement = 'ApiDocument';
    protected $_requestModel, $_responseModel;
    
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
        $deserializedObject = new Varien_Object();
        
        $xmlObject = Mage::helper('teamwork_common/parser')->deserializeXml($string);
        if($xmlObject)
        {
            foreach( $xmlObject->attributes() as $attributeKey => $attributeValue )
            {
                $deserializedObject->setData($attributeKey, (string)$attributeValue);
            }
            if(isset($xmlObject->Response))
            {
                $deserializedObject->setResponse($xmlObject->Response);
            }
        }
        return $deserializedObject;
    }
    
    public function convertDocumentIntoStaging($responseObject, $awaitingDocument)
    {
        $responseObject->addData( (array)$awaitingDocument->getData() ); // TODO: BAD APPROACH!!
        
        $this->_responseModel->getClassLoader($responseObject, $awaitingDocument)->parse();
    }
}