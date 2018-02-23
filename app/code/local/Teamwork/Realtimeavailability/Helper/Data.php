<?php
class Teamwork_Realtimeavailability_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function sortChannelByPriority($channel)
    {
        return ($channel == Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_DEFAULT_CHANNEL)) ? -1 : 1;
    }
    
    //WebOrderProcessingArea: 0 - WebOrders; 1 - SalesOrders. Ignore for SalesOrders.
    public function isSalesOrderType($weborder)
    {
        $store = Mage::getModel('core/store')->load( $weborder->getStoreId() );
        if( !empty($store) )
        {
            $resourceModel = Mage::getSingleton('teamwork_realtimeavailability/resource');
            $channelId = $resourceModel->getChannelId( $store->getCode() );
            if( empty($channelId) && Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_PROCESS_UNKNOWN_WEBORDERS) )
            {
                $channelId = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_UNKNOWN_WEBORDER_DEFAULT_CHANNEL);
            }
            
            if( !empty($channelId) )
            {
                $orderType = $resourceModel->getGlobalOrderType($channelId);
                return ($orderType === "0") ? false : true;
            }
        }
    }
    
    public function getWeborderGuid($order)
    {
        if( $order->getIncrementId() )
        {
            $webOrderId = Mage::getSingleton('teamwork_realtimeavailability/resource')->getOrderGuidByOrderNo( $order->getIncrementId() );
        }
        
        if( empty($webOrderId) )
        {
            $webOrderId = Mage::helper('teamwork_transfer')->generateGuid();
        }
        return $webOrderId;
    }
    
    public function getLocationId($order)
    {
        $resourceModel = Mage::getSingleton('teamwork_realtimeavailability/resource');
        $storeId = $order->getStoreId();
        $channelId = $resourceModel->getChannelId( Mage::app()->getStore($storeId)->getCode() );
        
        if( empty($channelId) && Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_PROCESS_UNKNOWN_WEBORDERS) )
        {
            $channelId = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_UNKNOWN_WEBORDER_DEFAULT_CHANNEL);
            if(!empty($channelId))
            {
                $storeId = $resourceModel->getStoreIdByChannelId($channelId);
            }
        }
        
        $locationId = Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_DEFAULT_LOCATION, $storeId);
        if( empty($locationId) && !empty($channelId) )
        { 
            $locationId = $resourceModel->simulateDefaultLocation( array(), $channelId );
        }
        return $locationId;
    }
}