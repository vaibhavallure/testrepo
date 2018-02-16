<?php
class Teamwork_Common_Model_Chq_Xml_Response
{
    public $chqStaging;
    protected $_loader;
    const RESPONSE_BASE = 'teamwork_common/chq_xml_response_';
    
    public function setChqStaging(Varien_Object $chqStaging)
    {
        $this->chqStaging = $chqStaging;
        $this->_loader = $this->getClassLoader();
    }
    
    public function parse()
    {
        $this->_loader->parse();
    }
    
    public function getClassLoader()
    {
        return Mage::getModel( self::RESPONSE_BASE . Teamwork_Common_Model_Chq_Api_Type::getClassByType($this->chqStaging->getData('ApiRequestType')), $this->chqStaging );
    }
}