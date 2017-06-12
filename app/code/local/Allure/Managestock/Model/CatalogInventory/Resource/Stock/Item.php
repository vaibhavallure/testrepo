<?php

class Allure_Managestock_Model_CatalogInventory_Resource_Stock_Item extends Mage_CatalogInventory_Model_Resource_Stock_Item
{
    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param int $productId
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
	public function loadByProductId(Mage_CatalogInventory_Model_Stock_Item $item, $productId)
	{
		//retrieve stock id by using current website..
		$stockId = Mage::helper('managestock')->getStockId();
		$select = $this->_getLoadSelect('product_id', $productId, $item)
			->where('stock_id = :stock_id');
		$data = $this->_getReadAdapter()->fetchRow($select, array(':stock_id' => $stockId));
		if ($data) {
			$item->setData($data);
		}
		$this->_afterLoad($item);
		return $this;
	}
	
	
	public function loadByProductIdAndStockId(Mage_CatalogInventory_Model_Stock_Item $item, $productId,$stockId)
	{
		$select = $this->_getLoadSelect('product_id', $productId, $item)
		->where('stock_id = :stock_id');
		$data = $this->_getReadAdapter()->fetchRow($select, array(':stock_id' => $stockId));
		if ($data) {
			$item->setData($data);
		}
		$this->_afterLoad($item);
		return $this;
	}

}
