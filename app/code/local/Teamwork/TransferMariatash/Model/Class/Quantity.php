<?php
class Teamwork_TransferMariatash_Model_Class_Quantity extends Teamwork_Transfer_Model_Class_Quantity
{
    protected function _getInventoryData($itemId, $quantity, $children=array())
    {
        
        /*$select = $this->_db->select()
            ->from( array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), array('sty.customlookup7') )
        ->join( array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), "sty.style_id = it.style_id  and it.item_id='{$itemId}'", array());*/
		
		$select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_attribute_set'), array('customlookup7'))
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
        else
        {
            $inventoryData = array(
                'use_config_backorders'     => 1,
                'use_config_manage_stock'   => $manageStock,
                'qty'                       => $quantity,
            );
        }

        if (strtolower($customLookup) != 'yes' && Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_STOCK_AVALIABILITY))
        {
            $inventoryData['is_in_stock'] = ( $quantity > 0 || $this->_getChildrenDependedStock($children) ) ?
                Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK :
            Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;
        }

        if( !$manageStock )
        {
            $inventoryData['manage_stock'] = $manageStock;
        }
        return $inventoryData;
    }
}