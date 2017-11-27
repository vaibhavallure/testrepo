<?php

class Allure_Managestock_Model_CatalogInventory_Resource_Stock_Item extends Mage_CatalogInventory_Model_Resource_Stock_Item
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

    public function updateModifiedDate ()
    {
        if (! $this->getId()) {
            return;
        }
        
        $tableName = $this->_getResource()->getTable(
                'cataloginventory/stock_item');
        
        $connection = Mage::getSingleton('core/resource')->getConnection(
                'core_write');
        $sql = 'UPDATE ' . $tableName .
                 ' SET `updated_at` = ? WHERE `item_id` = ?';
        $connection->query($sql, array(
                Varien_Date::now(),
                $this->getId()
        ));
    }

    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param int $productId
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function loadByProductId (
            Mage_CatalogInventory_Model_Stock_Item $item, $productId)
    {
        // retrieve stock id by using current website..
        $stockId = Mage::helper('managestock')->getStockId();
        $select = $this->_getLoadSelect('product_id', $productId, $item)->where(
                'stock_id = :stock_id');
        $data = $this->_getReadAdapter()->fetchRow($select,
                array(
                        ':stock_id' => $stockId
                ));
        if ($data) {
            $item->setData($data);
        }
        $this->_afterLoad($item);
        return $this;
    }

    public function loadByProductIdAndStockId (
            Mage_CatalogInventory_Model_Stock_Item $item, $productId, $stockId)
    {
        $select = $this->_getLoadSelect('product_id', $productId, $item)->where(
                'stock_id = :stock_id');
        $data = $this->_getReadAdapter()->fetchRow($select,
                array(
                        ':stock_id' => $stockId
                ));
        if ($data) {
            $item->setData($data);
        }
        $this->_afterLoad($item);
        return $this;
    }

    /**
     * Add join for catalog in stock field to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function addCatalogInventoryToProductCollection ($productCollection)
    {
        $adapter = $this->_getReadAdapter();
        $isManageStock = (int) Mage::getStoreConfig(
                Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $stockExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 1',
                $isManageStock, 'cisi.manage_stock');
        $stockExpr = $adapter->getCheckSql("({$stockExpr} = 1)",
                'cisi.is_in_stock', '1');
        
        $productCollection->joinTable(
                array(
                        'cisi' => 'cataloginventory/stock_item'
                ), 'product_id=entity_id',
                array(
                        'is_saleable' => new Zend_Db_Expr($stockExpr),
                        'inventory_in_stock' => 'is_in_stock'
                ), null, 'left');
        $productCollection->getSelect()->distinct();
        // Mage::log((string)
        // $productCollection->getSelect(),Zend_Log::DEBUG,'abc',true);
        return $this;
    }
}
