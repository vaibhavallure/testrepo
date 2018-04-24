<?php
class Teamwork_Realtimeavailability_Model_Resource extends Mage_Core_Model_Abstract
{
    static $twWeborderId = 'tw_guid';
    static $twDefaultLocation = 'tw_default_location';
	const RTA_URI_SETTING = 'RTQServerUrl';
    const SERVICE_SETTINGS_MODIFIED_TIME = 'rta_modified_time';
    
    public function __construct()
    {
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }
    //service_location_status
    public function getEnabledLocationsByStore($store)
    {
        $select = $this->_db->select()
            ->from( array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location')), array('code', 'location_id'))
            ->join( array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'loc.location_id = locst.location_id', null)
            ->join( array('ch' => Mage::getSingleton('core/resource')->getTableName('service_channel')), 'locst.channel_id = ch.channel_id', null)
            ->where("ch.channel_name = '{$store}'")
        ->where('locst.enabled = 1');
        return $this->_db->fetchPairs($select);
    }
    
    public function getEnabledLocationsForEachStore()
    {
        $sql = 'SELECT loc.code, locst.location_id,
        (
           SELECT COUNT(*)
               FROM ' . Mage::getSingleton('core/resource')->getTableName('service_location_status') . '
           WHERE location_id=locst.location_id AND enabled=1
               GROUP BY location_id
        ) cnt
        FROM ' . Mage::getSingleton('core/resource')->getTableName('service_location_status') . ' locst
        JOIN ' . Mage::getSingleton('core/resource')->getTableName('service_location') . ' loc ON locst.location_id=loc.location_id
           GROUP BY location_id
        HAVING COUNT(*)=cnt';
        return $this->_db->fetchPairs($sql);
    }
    
    public function getLocationCode($location_id)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_location');
        $select = $this->_db->select()
            ->from($table, array('code'))
        ->where('location_id = ?', $location_id);
        return $this->_db->fetchOne($select);
    }
    
    public function getOrderGuidByOrderNo($orderNo)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_weborder');
        $select = $this->_db->select()
            ->from($table, array('WebOrderId'))
        ->where('OrderNo = ?', $orderNo);
        return $this->_db->fetchOne($select);
    }
    
    public function getItemGuidPlu($entity_id)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_items');
        $select = $this->_db->select()
            ->from($table, array('item_id', 'plu'))
        ->where('internal_id = ?', $entity_id);
        
        return $this->_db->fetchRow($select);
    }
    
    public function getTimeSetting()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_settings');
        $select = $this->_db->select()
            ->from($table)
        ->where('setting_name = ?', self::SERVICE_SETTINGS_MODIFIED_TIME);
        
        $time = $this->_db->fetchRow($select);
        return $time ? $time : null;
    }
    
    public function writeInventory($inventory, $channel_id, $request_id)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_inventory');
        if( !empty($inventory['quantities']) )
        {
            foreach($inventory['quantities'] as $location)
            {
                $select = $this->_db->select()
                    ->from($table)
                    ->where('item_id = ?', $inventory['itemId'])
                    ->where('location_id = ?', $location['locationId'])
                ->where('channel_id = ?', $channel_id);
                
                $row = $this->_db->fetchRow($select);
                if( empty($row) )
                {
                    $data = array(
                        'item_id'       => $inventory['itemId'],
                        'location_id'   => $location['locationId'],
                        'channel_id'    => $channel_id,
                        'request_id'    => $request_id,
                        'quantity'      => $location['available']
                    );
                    $this->_db->insert($table, $data);
                }
                else
                {
                    $data = array(
                        'request_id'    => $request_id,
                        'quantity'      => $location['available']
                    );
                    $this->_db->update($table, $data, "item_id = '{$inventory['itemId']}' AND location_id = '{$location['locationId']}' AND channel_id = '{$channel_id}'");
                }
            }
        }
    }
    
    public function getChannels()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_channel');
        $select = $this->_db->select()->from($table, array('channel_id', 'channel_name'));
        return $this->_db->fetchPairs($select);
    }
    
    public function getChannelId($channel_name)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_channel');
        $select = $this->_db->select()->from($table, array('channel_id'))->where('channel_name = ?', $channel_name);
        return $this->_db->fetchOne($select);
    }
    
    public function getStoreIdByChannelId($channelId)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_channel');
        $select = $this->_db->select()->from($table, array('channel_name'))->where('channel_id = ?', $channelId);
        $name = $this->_db->fetchOne($select);
        if( !empty($name) )
        {
            $storeInfo = Mage::getModel('core/store')->loadConfig($name);
            if( !empty($storeInfo) )
            {
                return $storeInfo->getStoreId();
            }
        }
    }
    
    public function simulateDefaultLocation($items, $channelId)
    {
        $select = $this->_db->select()
            ->from( array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), null )
            ->join( array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'locst.location_id=inv.location_id AND inv.channel_id=locst.channel_id', array('location_id') )
            ->where('item_id in (?)', $items)
            ->where('locst.channel_id = ?', $channelId)
            ->where('locst.enabled=1')
            ->where('inv.quantity > 0')
            ->group('locst.location_id')
            ->order('COUNT(*) DESC, SUM(quantity) DESC')
        ->limit(1);
        $locationId = $this->_db->fetchOne($select);
        
        if( empty($locationId) )
        {
            $select = $this->_db->select()
                ->from( Mage::getSingleton('core/resource')->getTableName('service_location_status'), array('location_id') )
                ->where('channel_id = ?', $channelId)
                ->where('enabled=1')
            ->limit(1);
            $locationId = $this->_db->fetchOne($select);
        }
        return $locationId;
    }
    
    public function updateTimeSetting($time)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_settings');
        $currentSetting = $this->getTimeSetting();
        if( !empty($currentSetting) )
        {
            $this->_db->update($table, array('setting_value' => $time), "setting_name = '" . self::SERVICE_SETTINGS_MODIFIED_TIME . "'") ;
        }
        else
        {
            $this->_db->insert($table, array('setting_value' => $time, 'setting_name' => self::SERVICE_SETTINGS_MODIFIED_TIME, 'channel_id' => ''));
        }
    }
	
	public function getRtaUri($channel_id=null)
    {
		$table = Mage::getSingleton('core/resource')->getTableName('service_settings');
		$select = $this->_db->select()
            ->from($table, array('setting_value'))
        ->where('setting_name = ?', self::RTA_URI_SETTING);
		if( !empty($channel_id) )
		{
			$select->where('channel_id = ?', $channel_id);
		}
        return $this->_db->fetchOne($select);
	}
    
    public function checkItemChannelAvailability($item_id, $channels)
    {
		$select = $this->_db->select()
            ->from(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), array('it.channel_id', 'item_status' => 'it.ecomerce'))
            ->joinLeft(array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), 'it.style_id=sty.style_id and it.channel_id=sty.channel_id', array('style_status' => 'sty.ecomerce', 'sty.inventype'))
            ->where('item_id = ?', $item_id)
        ->where('it.channel_id IN (?)', $channels);
        $return = $this->_db->fetchAssoc($select);
        return $return ? $return : array();
	}
    
    public function getHungryMismatchedInventory()
    {
		$total = 'SUM(if(locst.enabled,inv.quantity,0))';
        if( !Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_NEGATIVE_INVENTORY) )
        {
            $total = 'SUM(GREATEST(if(locst.enabled,inv.quantity,0),0))';
        }
        $select = $this->_db->select()
            ->from(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), array('it.plu', 'it.internal_id', 'it.item_id', 'it.channel_id'))
            ->join(array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), 'sty.style_id=it.style_id AND sty.channel_id=it.channel_id', array())
            ->joinLeft(array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), 'it.item_id=inv.item_id AND it.channel_id=inv.channel_id', array('ECOMM_TOTAL' => new Zend_Db_Expr($total), 'RTA_QTY_BY_LOCATION' => new Zend_Db_Expr('GROUP_CONCAT(inv.quantity ORDER BY inv.location_id)')))
            ->joinLeft(array('st' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), 'st.product_id=it.internal_id', array('MAGENTO_QTY' => 'st.qty'))
            ->joinLeft(array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'locst.location_id=inv.location_id AND locst.channel_id=inv.channel_id', array('LOCATION_AVAILABLE' => new Zend_Db_Expr('GROUP_CONCAT(ifnull(locst.enabled,0) ORDER BY inv.location_id)')))
            ->join(array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location')), 'loc.location_id=inv.location_id', array('LOCATION_CODES' => new Zend_Db_Expr('GROUP_CONCAT(loc.code ORDER BY inv.location_id)')))
            ->where("sty.inventype != 'ServiceItem'")
            ->group('it.item_id')
            ->group('it.channel_id')
        ->having('ECOMM_TOTAL != MAGENTO_QTY');
        return $this->_db->fetchAll($select);
    }
    
    public function getSmartMismatchedInventory()
    {
        $total = 'SUM(if(locst.enabled,inv.quantity,0))';
        if( !Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_NEGATIVE_INVENTORY) )
        {
            $total = 'SUM(GREATEST(if(locst.enabled,inv.quantity,0),0))';
        }
        $wrong = $this->_db->select()
            ->from(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), array('it.plu', 'it.internal_id', 'it.item_id', 'it.channel_id'))
            ->join(array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), 'sty.style_id=it.style_id AND sty.channel_id=it.channel_id', array())
            ->joinLeft(array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), 'it.item_id=inv.item_id AND it.channel_id=inv.channel_id', array('ECOMM_TOTAL' => new Zend_Db_Expr($total), 'RTA_QTY_BY_LOCATION' => new Zend_Db_Expr('GROUP_CONCAT(inv.quantity ORDER BY inv.location_id)')))
            ->join(array('st' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), 'st.product_id=it.internal_id', array('MAGENTO_QTY' => 'st.qty'))
            ->joinLeft(array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'locst.location_id=inv.location_id AND locst.channel_id=inv.channel_id', array('LOCATION_AVAILABLE' => new Zend_Db_Expr('GROUP_CONCAT(ifnull(locst.enabled,0) ORDER BY inv.location_id)')))
            ->join(array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location')), 'loc.location_id=inv.location_id', array('LOCATION_CODES' => new Zend_Db_Expr('GROUP_CONCAT(loc.code ORDER BY inv.location_id)')))
            ->where("sty.inventype != 'ServiceItem'")
            ->group('it.item_id')
            ->group('it.channel_id')
        ->having('ECOMM_TOTAL != MAGENTO_QTY');
        
        $correct = $this->_db->select()
            ->from(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), array('it.plu', 'it.item_id', 'it.channel_id'))
            ->join(array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), 'it.item_id=inv.item_id AND it.channel_id=inv.channel_id', array('ECOMM_TOTAL' => new Zend_Db_Expr($total)))
            ->join(array('st' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), 'st.product_id=it.internal_id', array('MAGENTO_QTY' => 'st.qty'))
            ->join(array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'locst.location_id=inv.location_id AND locst.channel_id=inv.channel_id', array())
            ->group('it.item_id')
        ->group('it.channel_id');
            
        $select = $this->_db->select()
            ->from(array('wrong' => $wrong))
            ->joinleft(array('correct' => $correct), 'wrong.item_id=correct.item_id and wrong.channel_id!=correct.channel_id and correct.ECOMM_TOTAL=correct.MAGENTO_QTY', array())
            ->where('correct.plu is null')
        ->order(new Zend_Db_Expr('cast(wrong.plu as signed)'));

        return $this->_db->fetchAll($select);
    }
    
    public function getFullStagingInventoryPicture()
    {
        $total = 'SUM(if(locst.enabled,inv.quantity,0))';
        if( !Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_NEGATIVE_INVENTORY) )
        {
            $total = 'SUM(GREATEST(if(locst.enabled,inv.quantity,0),0))';
        }
        $full = $this->_db->select()
            ->from(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), array('it.plu', 'it.internal_id', 'it.item_id', 'it.channel_id'))
            ->join(array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), 'sty.style_id=it.style_id AND sty.channel_id=it.channel_id', array())
            ->joinLeft(array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), 'it.item_id=inv.item_id AND it.channel_id=inv.channel_id', array('ECOMM_TOTAL' => new Zend_Db_Expr($total), 'RTA_QTY_BY_LOCATION' => new Zend_Db_Expr('GROUP_CONCAT(inv.quantity ORDER BY inv.location_id)')))
            ->join(array('st' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), 'st.product_id=it.internal_id', array('MAGENTO_QTY' => 'st.qty'))
            ->joinLeft(array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'locst.location_id=inv.location_id AND locst.channel_id=inv.channel_id', array('LOCATION_AVAILABLE' => new Zend_Db_Expr('GROUP_CONCAT(ifnull(locst.enabled,0) ORDER BY inv.location_id)')))
            ->join(array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location')), 'loc.location_id=inv.location_id', array('LOCATION_CODES' => new Zend_Db_Expr('GROUP_CONCAT(loc.code ORDER BY inv.location_id)')))
            ->where("sty.inventype != 'ServiceItem'")
            ->group('it.item_id')
        ->group('it.channel_id');
        return $this->_db->fetchAll($full);
    }
    
    public function getActualInventory()
    {
        $select = $this->_db->select()
            ->from(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), array('it.item_id', 'it.plu', 'it.internal_id', 'channels' => new Zend_Db_Expr('GROUP_CONCAT(it.channel_id)')))
            ->join(array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), 'sty.style_id=it.style_id AND sty.channel_id=it.channel_id', array())
            ->join(array('st' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), 'st.product_id=it.internal_id', array('st.qty'))
            ->where("sty.inventype != 'ServiceItem'")
        ->group('it.item_id');
        return $this->_db->fetchAssoc($select);
    }
    
    public function getLocationsByStore($store)
    {
        $select = $this->_db->select()
            ->from( array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location')), array('code', 'location_id'))
            ->join( array('locst' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'loc.location_id = locst.location_id', null)
            ->join( array('ch' => Mage::getSingleton('core/resource')->getTableName('service_channel')), 'locst.channel_id = ch.channel_id', null)
            ->where("ch.channel_name = '{$store}'");
        return $this->_db->fetchPairs($select);
    }

	public function getCommitedInventoryUnknownForChq()
    {
        $select = $this->_db->select()
            ->from(array('ord' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order')), array())
            ->joinLeft(array('web' => Mage::getSingleton('core/resource')->getTableName('service_weborder')), 'ord.increment_id=web.OrderNo', array())
            ->join(array('ordit' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item')), 'ord.entity_id=ordit.order_id', array())
            ->join(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), 'ordit.product_id=it.internal_id', array('it.item_id','qty' => new Zend_Db_Expr('SUM(GREATEST(0,ordit.qty_ordered-ordit.qty_refunded-ordit.qty_canceled-ordit.qty_shipped))')))
            ->where("web.WebOrderId IS NULL")
        ->group('it.item_id');
        return $this->_db->fetchPairs($select);
    }
    
    public function getGlobalOrderType($channel_id)
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_settings'), array('setting_value'))
            ->where('setting_name = ?', 'WebOrderProcessingArea')
        ->where('channel_id = ?', $channel_id);

        return $this->_db->fetchOne($select);
    }
}