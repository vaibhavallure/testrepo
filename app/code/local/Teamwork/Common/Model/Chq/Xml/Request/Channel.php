<?php
class Teamwork_Common_Model_Chq_Xml_Request_Channel extends Teamwork_Common_Model_Chq_Xml_Request_Abstract
{
    protected function addSettings($addSettings=false)
    {
        $settings = parent::addSettings(true);
        $settings->addChild('IncludeEComCategoriesSetting', 'No');
    }
}