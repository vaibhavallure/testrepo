<?php
class Teamwork_Realtimeavailability_Model_Realtimeavailability extends Teamwork_Realtimeavailability_Model_Svs
{
    public $_itemLimitPerBatch = 500;
    public $_typeOfOrderRegistration = 'sale';
    public $resourceModel;

    const RTA_NEGATIVE_INVENTORY = 'teamwork_realtimeavailability/options/negative_inventory';
    const RTA_DEFAULT_CHANNEL = 'teamwork_realtimeavailability/options/default_channel';
    const RTA_DEFAULT_LOCATION = 'teamwork_realtimeavailability/options/default_location';
    const RTA_PATH = 'teamwork_realtimeavailability/options/path';

    public function __construct()
    {
        $this->resourceModel = Mage::getSingleton('teamwork_realtimeavailability/resource');
    }

    public function registerOrder($webOrder, $webOrderId, $locationId, $items)
    {
        sort($items); //remove associative keys
        $params = array(
            'documentId'        => $webOrderId,
            'docNum'            => $webOrder->getIncrementId(),
            'locationId'        => $locationId,
            'locationCode'      => $this->resourceModel->getLocationCode($locationId),
            'bin'               => $this->_typeOfOrderRegistration,
            'items'             => $items,
            'createDateTime'    => $webOrder->getCreatedAt(),
            'editDateTime'      => $webOrder->getUpdatedAt(),
            'customerName'      => trim($webOrder->getBillingAddress()->getFirstname() . " " . $webOrder->getBillingAddress()->getLastname())
        );
        $this->register($params);
        return $locationId;
    }

    public function changedInventory($globalVars=null, $transfer=null)
    {
        $setting = $this->resourceModel->getTimeSetting();
        $time = !empty($setting['setting_value']) ? $setting['setting_value'] : null;

        $channels = $this->getChannelRequestId($globalVars);

        if( !empty($channels) )
        {
            $newTime = null;
            $update = false;
            $itemAssignments = array();
            $dummyRequestId = Mage::helper('teamwork_transfer')->generateGuid();
            
            $xmlItemInfo = array();
            do
            {
                $params = array(
                    'cursor'                    => !empty($xmlItemInfo['cursor']) ? $xmlItemInfo['cursor'] : null,
                    'modifiedAfterTime'         => $time,
                    'limit'                     => $this->_itemLimitPerBatch,
                    'itemIdentifierType'        => 'Id',
                    'locationIdentifierType'    => 'Id',
                );
                $xmlItemInfo = (array)$this->changedBatch($params);

                if( $transfer instanceof Teamwork_Transfer_Model_Transfer)
                {
                    $transfer->checkLastUpdateTime();
                }

                if( !empty($xmlItemInfo['itemQuantities']) )
                {
                    $processedItems = array();
                    foreach($channels as $channel_id => $request_id)
                    {
                        $allowProcess = array();
                        foreach($xmlItemInfo['itemQuantities'] as $inventory)
                        {
                            $itemId = $inventory['itemId'];
                            if( !in_array($itemId, $processedItems) )
                            {
                                $allowProcess[$itemId] = true;
                            }

                            if( !empty($allowProcess[$itemId]) && !isset($itemAssignments[$itemId]) )
                            {
                                $this->_getItemAssignment($channels, $itemAssignments, $itemId);
                            }

                            // TODO: REMOVE HARDCODE IN FUTURE VERSION - 'EC Offer'
                            /* Processed only:
                                1) Still not proccessed products: $allowProcess
                                2) 'EC Offer' or Multiple bound products which 'EC Offer' not in any channel
                            */
                            if( !empty($allowProcess[$itemId]) && isset($itemAssignments[$itemId][$channel_id]) &&
                                ((strtolower($itemAssignments[$itemId][$channel_id]) == 'ec offer') || ((count($itemAssignments[$itemId]) > 1) && !in_array('ec offer', array_map('strtolower', $itemAssignments[$itemId]) ))  || (count($itemAssignments[$itemId]) == 1 && empty($globalVars['channel_id'])) )
                            )
                            {
                                $processedItems[] = $itemId;
                                $update = true;

                                $this->resourceModel->writeInventory($inventory, $channel_id, $request_id);
                            }
                            else
                            {
                                $this->resourceModel->writeInventory($inventory, $channel_id, $dummyRequestId);
                            }
                        }
                    }
                }
                $newTime = !empty($xmlItemInfo['lastUpdateTime']) ? $xmlItemInfo['lastUpdateTime'] : $newTime;
            }
            while( !empty($xmlItemInfo['cursor']) );

            if( !empty($newTime) )
            {
                $this->resourceModel->updateTimeSetting($newTime);
            }
            
            if( $update && !$globalVars )
            {
                $this->processQty($channels);
            }
        }
    }

    public function getChannelRequestId($globalVars=null)
    {
        if( !empty($globalVars) )
        {
            return array( $globalVars['channel_id'] => $globalVars['request_id']);
        }

        $channels = $this->resourceModel->getChannels();

        if( Mage::getStoreConfig(self::RTA_DEFAULT_CHANNEL) )
        {
            uksort($channels, array(Mage::helper('teamwork_realtimeavailability'), 'sortChannelByPriority'));
        }

        $return = array();
        foreach($channels as $channelId => $channelName)
        {
            $return[$channelId] = Mage::helper('teamwork_transfer')->generateGuid();
        }
        return $return;
    }

    public function processQty($channels)
    {
        foreach($channels as $channel_id => $request_id)
        {
            $quantityModel = Mage::getModel('teamwork_transfer/class_quantity');
            $quantityModel->mode = $quantityModel::SCHEDULER_MODE;
            $quantityModel->init( array('channel_id' => $channel_id, 'request_id' => $request_id) );
            $quantityModel->execute();
        }
    }

    public function getInventory($itemGuids)
    {
		$params = array(
            'items'                     => $itemGuids,
            'itemIdentifierType'        => 'Id', // TODO: "Id", "PLU", "UPC", "EID"
            'locationIdentifierType'    => 'Id', // TODO: "Id", "Code", "EID"
        );
        return $this->partialBatch($params);
    }

    protected function _getItemAssignment($channels, &$itemAssignments, $itemId)
    {
		$requestedChannels = array_keys($channels);
        $itemAssignments[$itemId] = $this->resourceModel->checkItemChannelAvailability($itemId, $requestedChannels);
        if(!empty($itemAssignments[$itemId]))
        {
            foreach($itemAssignments[$itemId] as $assignedChannelKey => $assignedChannel)
            {
                if( Mage::getModel('teamwork_transfer/class_item')->getProductTypeByInventype($assignedChannel['inventype']) == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE )
                {
                    $itemAssignments[$itemId][$assignedChannelKey] = $assignedChannel['style_status'];
                }
                else
                {
                    $itemAssignments[$itemId][$assignedChannelKey] = $assignedChannel['item_status'];
                }
            }
        }
    }
}