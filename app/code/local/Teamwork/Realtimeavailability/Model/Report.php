<?php
class Teamwork_Realtimeavailability_Model_Report extends Mage_Core_Model_Abstract
{
    public $resourceModel, $rtaModel, $request;
    public $channels=array(), $channelLocations=array(), $channelAllLocations=array(), $stagingMismatchedItems=array(), $rtaMismatchedItems=array(), $magentoItemsInventory=array(), $rtaItems=array(), $rtaQuantities = array();
    const FULL_RTA_REPORT = 'fullrtareport';
    const FULL_STAGING_REPORT = 'fullstagingreport';
    const RECORT_RTA_LOGS = 'recordrta';
    
    public function _construct()
    {
        $this->resourceModel = Mage::getSingleton('teamwork_realtimeavailability/resource');
        $this->rtaModel = Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability');
        $this->buildChannels();
        $this->buildLocations();
        $this->request = Mage::app()->getRequest();
    }
    
    public function buildChannels()
    {
        $this->channels = $this->resourceModel->getChannels();
    }
        
    public function buildLocations()
    {  
        foreach($this->channels as $channelGuid => $channelName)
        {
            $this->channelLocations[$channelGuid] = $this->resourceModel->getEnabledLocationsByStore($channelName);
            $this->channelAllLocations[$channelGuid] = $this->resourceModel->getLocationsByStore($channelName);
        }
    }
    
    public function getStagingMismatchedItems()
    {
        if( !empty($this->channels) )
        {
            if( $this->request->getParam(self::FULL_STAGING_REPORT, false) )
            {
                $this->stagingMismatchedItems = $this->resourceModel->getFullStagingInventoryPicture();
            }
            elseif( count($this->channels) == 1  )
            {
                $this->stagingMismatchedItems = $this->resourceModel->getHungryMismatchedInventory();
            }
            elseif( count($this->channels)>1 )
            {
                $this->stagingMismatchedItems = $this->resourceModel->getSmartMismatchedInventory();
            }
        }
        return $this->stagingMismatchedItems;
    }
    
    public function _getRtaInventory()
    {
        $itemsForRequest = array_keys( $this->magentoItemsInventory );
        $step = $this->rtaModel->_itemLimitPerBatch;
        
        $recordLogger = false;
        if( $this->request->getParam(self::RECORT_RTA_LOGS, false) )
        {
            $recordLogger = true;
        }
        $this->rtaModel->recordLogs = $recordLogger;
        
        for($i=0,$j=count($itemsForRequest); $i<=$j; $i=$i+$step)
        {
            $guids = array_slice($itemsForRequest, $i, $step);
            $responseInfo = $this->rtaModel->getInventory( $guids );
            if( !empty($responseInfo['itemQuantities']) )
            {
                foreach($responseInfo['itemQuantities'] as $responseItem)
                {
                    $this->rtaQuantities[$responseItem['itemId']] = $responseItem['quantities'];
                }
            }
        }
    }
    
    public function getRtaMismatchedItems()
    {
        $this->magentoItemsInventory = $this->resourceModel->getActualInventory();
        $this->_getRtaInventory( );
        
        foreach($this->magentoItemsInventory as $magentoItemGuid => $magentoItemInventory)
        {
            $this->rtaMismatchedItems[$magentoItemGuid] = array();
            foreach($this->channelLocations as $channelId => $enabledLocations)
            {
                if(stripos($magentoItemInventory['channels'],$channelId) !== FALSE)
                {
                    $this->rtaItems[$magentoItemGuid][$channelId] = 0;
                    if( !empty($this->rtaQuantities[$magentoItemGuid]) )
                    {
                        foreach($this->rtaQuantities[$magentoItemGuid] as $rtaLocationInfo)
                        {
                            if(in_array($rtaLocationInfo['locationId'], $enabledLocations))
                            {
                                if(!Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_NEGATIVE_INVENTORY) && (int)$rtaLocationInfo['available']<0)
                                {
                                    continue;
                                }
                                $this->rtaItems[$magentoItemGuid][$channelId] += (int)$rtaLocationInfo['available'];
                            }
                        }
                    }
                    
                    if( !$this->request->getParam(self::FULL_RTA_REPORT, false) && $this->rtaItems[$magentoItemGuid][$channelId] == $magentoItemInventory['qty'] )
                    {
                        unset( $this->rtaMismatchedItems[$magentoItemGuid] );
                        break;
                    }
                    else
                    {
                        $this->rtaMismatchedItems[$magentoItemGuid][$channelId] = $this->rtaItems[$magentoItemGuid][$channelId];
                    }
                }
            }
        }
    }
    
    public function prepareRtaTitle()
    {
        $return = '';
        foreach($this->channels as $channelId => $channelName)
        {
            $return .= "<td>RTA Qty: {$channelName}({$channelId})</td>";
        }
        return $return;
    }
    
    public function prepareRtaColoumns($mismatchedItemId, $magentoQty)
    {
        $return = '';
        foreach($this->channels as $channelId => $channelName)
        {
            $locationInfo = '';
            if(isset($this->rtaMismatchedItems[$mismatchedItemId][$channelId]) && $this->rtaMismatchedItems[$mismatchedItemId][$channelId] != $magentoQty)
            {
                $style = " style='background-color:ffcccc;'";
                if(!empty($this->rtaQuantities[$mismatchedItemId]))
                {
                    foreach($this->rtaQuantities[$mismatchedItemId] as $rtaLocationInfo)
                    {
                        if( array_search($rtaLocationInfo['locationId'], $this->channelLocations[$channelId]) != FALSE)
                        {
                            $active = 'active';
                            if( !Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_NEGATIVE_INVENTORY) && (int)$rtaLocationInfo['available']<0 )
                            {
                                $active = 'ignored';
                            }
                        }
                        else
                        {
                            $active = 'inactive';
                        }
                        $location = array_search($rtaLocationInfo['locationId'], $this->channelAllLocations[$channelId]);
                        
                        if($active == 'active')
                        {
                            $locationInfo .= '<b>';
                        }
                        $locationInfo .= "{$location} ({$active}): 
                            onHand: {$rtaLocationInfo['onHand']}
                            committed: {$rtaLocationInfo['committed']}
                            available: {$rtaLocationInfo['available']}<br>
                        ";
                        if($active == 'active')
                        {
                            $locationInfo .= '</b>';
                        }
                    }
                }
                $result = $this->rtaMismatchedItems[$mismatchedItemId][$channelId];
            }
            else
            {
                $result = isset($this->rtaMismatchedItems[$mismatchedItemId][$channelId]) ? $this->rtaMismatchedItems[$mismatchedItemId][$channelId] : '-';
                $style = isset($this->rtaMismatchedItems[$mismatchedItemId][$channelId]) ? '' : " style='background-color:ccc;'";
            }
            $return .= "<td{$style}><b>{$result}</b><br/>{$locationInfo}</td>";
        }
        return $return;
    }

	public function getMagentoItems()
    {
        return $this->resourceModel->getActualInventory();
    }
}