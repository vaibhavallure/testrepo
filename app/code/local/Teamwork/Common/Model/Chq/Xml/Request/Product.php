<?php
class Teamwork_Common_Model_Chq_Xml_Request_Product extends Teamwork_Common_Model_Chq_Xml_Request_Abstract
{
    protected $_supportedEcTypes = array('EcOffer','EcSuspended','EcDiscontinued');
    protected function addTop()
    {
        parent::addTop();
        $this->_xmlElement->Request->addChild('Top', Mage::helper('teamwork_common/adminsettings')->getEntitiesPerButch());
    }
    
    protected function addSkip()
    {
        if($this->_requestData->getProcessed())
        {
            $this->_xmlElement->Request->addChild('Skip', $this->_requestData->getProcessed());
        }
    }
    
    protected function addFilters($addRecModified=true)
    {
        $filters = parent::addFilters($addRecModified);
        
        $styleFilter = $filters->addChild('Filter');
        $styleFilter->addAttribute('Field', 'ECType');
        $styleFilter->addAttribute('Operator', 'Contains');
        $styleFilter->addAttribute('Value', implode(',', $this->_supportedEcTypes));
        
        $styleFilter = $filters->addChild('Filter');
        $styleFilter->addAttribute('Field', 'Active');
        $styleFilter->addAttribute('Operator', 'Equal');
        $styleFilter->addAttribute('Value', 'true');
        
        $itemFilter = $filters->addChild('Filter');
        $itemFilter->addAttribute('Field', 'Item.ECType');
        $itemFilter->addAttribute('Operator', 'Contains');
        $itemFilter->addAttribute('Value', implode(',', $this->_supportedEcTypes));
    }
    
    protected function addSettings($addSettings=false)
    {
        $settings = parent::addSettings(true);
        $settings->addChild('ExcludePricesSetting', 'Yes');
    }
}