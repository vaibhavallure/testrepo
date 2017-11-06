<?php
class Alluremultistore_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Get adminhtml helper
	 *
	 * @return Alluremultistore_Catalog_Helper_Adminhtml
	 */
	public function getAdminhtmlHelper()
	{
		return Mage::helper('alluremultistore_catalog/adminhtml');
	}
	
	/**
	 * Get config
	 *
	 * @return Alluremultistore_Catalog_Model_Config
	 */
	public function getConfig()
	{
		return Mage::getSingleton('alluremultistore_catalog/config');
	}
	
	/**
	 * 
	 *
	 * @param int $stockId
	 *
	 * @return string
	 */
	public function getWebsiteTitleByStockId($stockId)
	{
		$website = Mage::getModel("core/website")->load($stockId,'stock_id');
		if ($website) {
			return $website->getName();
		} else {
			return null;
		}
	}
	
	public function getStockIds(){
		$collection = Mage::getModel("core/website")->getCollection()
			->addFieldToFilter('stock_id',array('neq'=>0));
		$ids = array();
		foreach ($collection as $stock){
			array_push($ids, $stock->getStockId());
		}
		return $ids;
	}
	
	
	public function getStoreIdsByUsingStockIds(){
		$stockIds = $this->getStockIds();
		$storeIds = array();
		foreach ($stockIds as $stockId){
			$websiteId = Mage::getModel("core/website")->load($stockId,'stock_id')->getWebsiteId();
			$storeId = Mage::getModel("core/store")->load($websiteId,'website_id')->getStoreId();
			$storeIds[$stockId] = $storeId;
		}
		return $storeIds;
	}
	
	public function getProductPriceHelper()
	{
		return $this->getProductHelper()->getPriceHelper();
	}
	
	public function getProductHelper()
	{
		return Mage::helper('alluremultistore_catalog/catalog_product');
	}
	
	/**
	 * check product has custom option present or not
	 * for parent child
	 */
	public function isCustomOptionsAvailable($productId){
	    $product   = Mage::getModel("catalog/product")->load($productId);
	    $isOptions = false;
	    if (count($product->getOptions()) > 0){
	        $isOptions = true;
	    }
	    return $isOptions;
	}
	
	
}