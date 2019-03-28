<?php

class Teamwork_Service_DamController extends Mage_Core_Controller_Front_Action
{

    public function _construct()
    {
        Mage::helper('teamwork_service')->fatalErrorObserver();
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', '1');
        set_time_limit(0);
        ob_start();
    }

    public function getupdateAction()
    {
        if (!Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_DAM_ENABLED)) return;
        $damMarker = Mage::getModel('teamwork_service/dam')->updateRecords();
        if ($damMarker && Mage::getResourceModel('teamwork_service/dam_style_collection')->addDAMMarkerFilter($damMarker)->count())
        {
            //call transfer to process records marked $damTimestamp
            $configObject = Mage::getConfig();
            if($configObject->getNode('teamwork_service/is_async_type'))
            {
                $timeout = 20;
            }
            else
            {
                $timeout = 0;
            }
            $stores = array_keys(Mage::app()->getStores());
            $url = Mage::getUrl(trim((string)$configObject->getNode('teamwork_service/dam_update_url'),"/"), array('_secure' => true, '_store' => reset($stores), '_nosid' => true));
            Mage::helper('teamwork_service')->makeCurlRequest($damMarker, 'dam_marker', $timeout, $url);
        }
    }

}
