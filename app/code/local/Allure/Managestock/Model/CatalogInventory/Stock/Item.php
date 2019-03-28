<?php

class Allure_Managestock_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{

    const BACKORDERS_YES = 'Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item';

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock ()
    {
        if (Mage::registry(
                Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES)) {
            return true;
        }
        
        return parent::getIsInStock();
    }

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders ()
    {
        if (Mage::registry(
                Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES)) {
            return Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY;
        }
        
        return parent::getBackorders();
    }

    /**
     * Retrieve stock identifier
     *
     * @todo multi stock
     * @return int
     */
    public function getStockId ()
    {
        return Mage::helper('managestock')->getStockId();
    }

    /**
     * Adding stock data to product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function assignProduct (Mage_Catalog_Model_Product $product)
    {
        if (! $this->getId() || ! $this->getProductId()) {
            $this->_getResource()->loadByProductId($this, $product->getId());
            $this->setOrigData();
        }
        
        $this->setProduct($product);
        $product->setStockItem($this);
        
        // set current store into session variable.
        // Mage::helper('managestock')->setStoreId($product->getStoreId());
        
        $product->setIsInStock($this->getIsInStock());
        // Mage::log($product->getCustStoreId(),Zend_Log::DEBUG,'abc',true);
        Mage::getSingleton('cataloginventory/stock_status')->assignProduct(
                $product, $this->getStockId(), $this->getStockStatus());
        return $this;
    }

    public function assignProductToNewStockByScript (
            Mage_Catalog_Model_Product $product, $stockId)
    {
        if (! $this->getId() || ! $this->getProductId()) {
            $this->_getResource()->loadByProductIdAndStockId($this,
                    $product->getId(), $stockId);
            $this->setOrigData();
        }
        
        $this->setProduct($product);
        $product->setStockItem($this);
        
        $product->setIsInStock($this->getIsInStock());
        // Mage::log($product->getCustStoreId(),Zend_Log::DEBUG,'abc',true);
        Mage::getSingleton('cataloginventory/stock_status')->assignProduct(
                $product, $stockId, $this->getStockStatus());
        return $this;
    }

    public function getProductStockOfWebsite (
            Mage_Catalog_Model_Product $product, $stockId)
    {
        return $this->_getResource()->loadByProductIdAndStockId($this,
                $product->getId(), $stockId);
        // return $this;
    }

    /**
     * Load item data by product
     *
     * @param mixed $product
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function loadByProductAndStock ($product, $stockId)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }
        $this->_getResource()->loadByProductIdAndStockId($this, $product,
                $stockId);
        $this->setOrigData();
        return $this;
    }

    public function updateModifiedDate ()
    {
        if (! $this->getId()) {
            return;
        }
        $stockId = $this->getStockId();
        $tableName = $this->_getResource()->getTable(
                'cataloginventory/stock_item');
        
        $connection = Mage::getSingleton('core/resource')->getConnection(
                'core_write');
        $sql = 'UPDATE ' . $tableName .
                 ' SET `updated_at` = ? WHERE `item_id` = ? AND `stock_id`=?';
        $connection->query($sql,
                array(
                        Varien_Date::now(),
                        $this->getId(),
                        $stockId
                ));
    }
}
