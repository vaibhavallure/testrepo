<?php
class Teamwork_Weborder_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getsimpleweborder($params = false)
    {
        Teamwork_Weborder_Helper_Log::logApiRequest(__METHOD__, $params);
        
        $response = Mage::getModel('teamwork_weborder/weborder')->generateXml($params);
        
        Teamwork_Weborder_Helper_Log::logApiResponse(__METHOD__, $params, $response);
        return $response;
    }
}