<?php
class Teamwork_TransferMariatash_Model_Class_Quantity extends Teamwork_Transfer_Model_Class_Quantity
{
    protected function _getInventoryData($itemId, $quantity, $children=array())
    {
		
		$select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_items'), array('customlookup7'))
        ->where('item_id = ?', $itemId);
        
		$customLookup = $this->_db->fetchOne($select);
        
        $manageStock = 1;
        if(!$children)
        {
            $manageStock = $this->checkManageStock($itemId);
        }
		
        if (strtolower($customLookup) == 'yes')
        {
            $inventoryData = array(
                'use_config_backorders'     => 0,
                'backorders'                => 1,
                'use_config_manage_stock'   => $manageStock,
                'qty'                       => $quantity,
				'is_in_stock'				=> Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK
            );
        }
		elseif (strtolower($customLookup) == 'notify customer')
        {
            $inventoryData = array(
                'use_config_backorders'     => 0,
                'backorders'                => 2,
                'use_config_manage_stock'   => $manageStock,
                'qty'                       => $quantity,
				'is_in_stock'				=> Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK
            );
        }
        else
        {
            $inventoryData = array(
                'use_config_backorders'     => 1,
                'use_config_manage_stock'   => $manageStock,
                'qty'                       => $quantity,
            );
			
			if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_STOCK_AVALIABILITY))
			{
				$inventoryData['is_in_stock'] = ( $quantity > 0 || $this->_getChildrenDependedStock($children) ) ?
					Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK :
				Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;
			}
        }

        if( !$manageStock )
        {
            $inventoryData['manage_stock'] = $manageStock;
        }
        return $inventoryData;
    }
}