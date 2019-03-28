<?php
class Teamwork_Realtimeavailability_Model_Observer extends Mage_Core_Model_Abstract
{
    public function scheduleTask()
    {
        Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability')->changedInventory();
    }
    
    public function registerSvsOrder($observer)
    {
        if( ($webOrderId = $observer['order']->getData(Teamwork_Realtimeavailability_Model_Resource::$twWeborderId))
            && ($locationId = $observer['order']->getData(Teamwork_Realtimeavailability_Model_Resource::$twDefaultLocation))
        )
        {
            if( $observer['order']->getOrigData('entity_id') )
            {
                if( Mage::helper('teamwork_transfer/webstaging')->isChqZoneUsedAsProcessing() && Mage::helper('teamwork_realtimeavailability')->isSalesOrderType($observer['order']) )
                {
                    $orderGuid = Mage::getSingleton('teamwork_realtimeavailability/resource')->getOrderGuidByOrderNo( $observer['order']->getIncrementId() );
                    if($orderGuid)
                    {
                        return;
                    }
                }
                elseif( !Mage::helper('teamwork_transfer/webstaging')->isChqZoneUsedAsProcessing() )
                {
                    return;
                }
            }
            
            $isOrderItemsChanged = false;
            $orderItems = array();
            foreach($observer['order']->getItemsCollection() as $item)
            {
                if(!$item->isDummy())
                {
                    $original_qty = max( ((float)$item->getOrigData('qty_ordered') - (float)$item->getOrigData('qty_refunded') - (float)$item->getOrigData('qty_canceled') - (float)$item->getOrigData('qty_shipped')), (float)0 );
                    $qty = max( ((float)$item->getData('qty_ordered') - (float)$item->getData('qty_refunded') - (float)$item->getData('qty_canceled') - (float)$item->getData('qty_shipped')), (float)0 );
                    
                    if($original_qty != $qty)
                    {
                        $isOrderItemsChanged = true;
                    }
                    
                    $productId = $item->getProductId();
                    if( $item->getHasChildren() )
                    {
                        $children = $item->getChildrenItems();
                        $productId = $children[0]->getProductId();
                    }
                    
                    $itemInfo = Mage::getSingleton('teamwork_realtimeavailability/resource')->getItemGuidPlu( $productId );
                    if( !empty($itemInfo) )
                    {
                        $quantity = !empty( $orderItems[$itemInfo['item_id']] ) ? $orderItems[$itemInfo['item_id']]['quantity'] + $qty : $qty;
                        if($quantity > 0)
                        {
                            $orderItems[$itemInfo['item_id']] = array(
                                'itemId'        => $itemInfo['item_id'],
                                'plu'           => $itemInfo['plu'],
                                'quantity'      => $quantity,
                            );
                        }
                    }
                }
            }
            
            if( $isOrderItemsChanged )
            {
                Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability')->registerOrder( $observer['order'], $webOrderId, $locationId, $orderItems );
            }
        }
    }
    
    public function setRtaOrderAttributes($observer)
    {
        $webOrderId = $observer['order']->getData(Teamwork_Realtimeavailability_Model_Resource::$twWeborderId);
        if( empty($webOrderId) )
        {
            $webOrderId = Mage::helper('teamwork_realtimeavailability')->getWeborderGuid($observer['order']);
           
            $observer['order']->setData( Teamwork_Realtimeavailability_Model_Resource::$twWeborderId, $webOrderId );
            Mage::getResourceModel('sales/order')->saveAttribute($observer['order'], Teamwork_Realtimeavailability_Model_Resource::$twWeborderId);
        }
        
        $locationId = $observer['order']->getData(Teamwork_Realtimeavailability_Model_Resource::$twDefaultLocation);
        if( empty($locationId) )
        {
            $locationId = Mage::helper('teamwork_realtimeavailability')->getLocationId($observer['order']);
           
            $observer['order']->setData( Teamwork_Realtimeavailability_Model_Resource::$twDefaultLocation, $locationId );
            Mage::getResourceModel('sales/order')->saveAttribute($observer['order'], Teamwork_Realtimeavailability_Model_Resource::$twDefaultLocation);
        }
    }
    
    public function addWebOrderData($observer)
    {
        $observer['weborder']['WebOrderId'] = $observer['order']->getData(Teamwork_Realtimeavailability_Model_Resource::$twWeborderId);
        $observer['weborder']['DefaultLocationId'] = $observer['order']->getData(Teamwork_Realtimeavailability_Model_Resource::$twDefaultLocation);
    }
}