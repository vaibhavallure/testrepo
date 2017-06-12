<?php

class Allure_Managestock_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
     * Retrieve stock identifier
     *
     * @todo multi stock
     * @return int
     */
    public function getStockId()
    {
    	//Mage::log(Mage::helper('managestock')->getStockId(),Zend_Log::DEBUG,'abc',true);
    	return Mage::helper('managestock')->getStockId();
    }

    
    /**
     * Adding stock data to product
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function assignProduct(Mage_Catalog_Model_Product $product)
    {
    	if (!$this->getId() || !$this->getProductId()) {
    		$this->_getResource()->loadByProductId($this, $product->getId());
    		$this->setOrigData();
    	}
    
    	$this->setProduct($product);
    	$product->setStockItem($this);
    
    	//set current store into session variable.
    	//Mage::helper('managestock')->setStoreId($product->getStoreId());
    	
    	$product->setIsInStock($this->getIsInStock());
    	//Mage::log($product->getCustStoreId(),Zend_Log::DEBUG,'abc',true);
    	Mage::getSingleton('cataloginventory/stock_status')
    	->assignProduct($product, $this->getStockId(), $this->getStockStatus());
    	return $this;
    }
    
    
    public function assignProductToNewStockByScript(Mage_Catalog_Model_Product $product,$stockId){
    	if (!$this->getId() || !$this->getProductId()) {
    		$this->_getResource()->loadByProductIdAndStockId($this, $product->getId(),$stockId);
    		$this->setOrigData();
    	}
    	
    	$this->setProduct($product);
    	$product->setStockItem($this);
    	
    	$product->setIsInStock($this->getIsInStock());
    	//Mage::log($product->getCustStoreId(),Zend_Log::DEBUG,'abc',true);
    	Mage::getSingleton('cataloginventory/stock_status')
    	->assignProduct($product, $stockId, $this->getStockStatus());
    	return $this;
    }
    
    public function getProductStockOfWebsite(Mage_Catalog_Model_Product $product,$stockId){
    	return  $this->_getResource()->loadByProductIdAndStockId($this, $product->getId(),$stockId);
    	//return $this;
    }
}
