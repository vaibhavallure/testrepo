<?php
class Teamwork_Common_Model_Chq_Xml_Request_Abstract
{
    protected $_xmlElement, $_requestData;
    
    public function __construct(Varien_Object $requestData)
    {
        $this->_requestData = $requestData;
    }
    
    public function generateRequestHeader()
    {
        $header = '<?xml version="1.0" encoding="utf-8"?><' . Teamwork_Common_Model_Chq_Xml::$rootElement . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/" />';
        $this->_xmlElement = new SimpleXMLElement($header);
        $this->_xmlElement->addAttribute('ApiDocumentId', $this->_requestData->getDocumentId());
        $this->_xmlElement->addChild('Request');
    }
    
    public function generateRequestBody()
    {
		$this->addSettings();
        $this->addParentApiDocumentId();
        $this->addFilters();
        $this->addSortDescriptions();
        $this->addTop();
        $this->addSkip();
    }
    
    public function getGeneratedXml()
    {
        return (string)$this->_xmlElement->asXML();
    }
    
    protected function addParentApiDocumentId()
    {
        if($this->_requestData->getParentDocumentId())
        {
            $this->_xmlElement->Request->addChild('ParentApiDocumentId', $this->_requestData->getParentDocumentId());
        }
    }
    
    protected function addSettings($addSettings=false)
    {
        if($addSettings)
        {
            return $this->_xmlElement->Request->addChild('Settings');
        }
    }
    
    protected function addFilters($addRecModified=true)
    {
        $filters = $this->_xmlElement->Request->addChild('Filters');
        if($addRecModified && $this->_requestData->getMaxDate())
        {
            $filter = $filters->addChild('Filter');
            $filter->addAttribute('Field', 'RecModified');
            $filter->addAttribute('Operator', 'Greater than');
            $filter->addAttribute('Value', $this->_requestData->getMaxDate());
        }
        return $filters;
    }
    
    protected function addSortDescriptions($addSortDescription=true)
    {
        $sortDescriptions = $this->_xmlElement->Request->addChild('SortDescriptions');
        if($sortDescriptions)
        {
            $sortDescription = $sortDescriptions->addChild('SortDescription');
            $sortDescription->addAttribute('Name', 'RecModified');
            $sortDescription->addAttribute('Direction', 'Ascending');
        }
        return $sortDescriptions;
    }
    
    protected function addTop()
    {}
    
    protected function addSkip()
    {}
}