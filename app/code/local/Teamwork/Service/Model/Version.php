<?php
class Teamwork_Service_Model_Version extends Mage_Core_Model_Abstract
{
    protected $_conn;

    public function _construct()
    {
        header('Content-Type: text/xml');
    }

    public function generateXml()
    {
        $version = '<?xml version="1.0" encoding="UTF-8"?>';
        $version .= '<PluginInformation Name="Teamwork Plug-in for Magento." Version="'.$this->getVersion().'"> Description of Plug-in. Plug-in for Magento '.Mage::getVersion().' created by Teamwork Retailer Co. </PluginInformation>';
        return base64_encode($version); 
    }

    protected function getVersion()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $version = $modules->Teamwork_Service->version;
        return $version;
    }
}