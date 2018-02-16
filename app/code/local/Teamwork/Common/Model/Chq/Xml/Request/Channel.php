<?php
class Teamwork_Common_Model_Chq_Xml_Request_Channel extends Teamwork_Common_Model_Chq_Xml_Request_Abstract
{
    protected function addSettings()
    {
        $settings = $this->_xmlElement->Request->addChild('Settings');
        $settings->addChild('IncludeEComCategoriesSetting', 'No');
    }
}