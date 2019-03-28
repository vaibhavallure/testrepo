<?php

class Allure_Catalog_Helper_Adminhtml extends Mage_Core_Helper_Abstract
{

    /**
     * Get warehouse helper
     *
     * @return Allurewarehouse_Warehouse_Helper_Data
     */
    public function getHelper ()
    {
        return Mage::helper('allure_catalog');
    }

    /**
     * Add column relation to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    protected function addColumnRelationToCollection ($collection, $column)
    {
        if (! $column->getRelation()) {
            return $this;
        }
        $relation = $column->getRelation();
        $fieldAlias = $column->getId();
        $fieldName = $relation['field_name'];
        $fkFieldName = $relation['fk_field_name'];
        $refFieldName = $relation['ref_field_name'];
        $tableAlias = $relation['table_alias'];
        $table = $collection->getTable($relation['table_name']);
        $collection->addFilterToMap($fieldAlias, $tableAlias . '.' . $fieldName);
        $collection->getSelect()->joinLeft(array(
                $tableAlias => $table
        ), '(main_table.' . $fkFieldName . ' = ' . $tableAlias . '.' .
                 $refFieldName . ')',
                        array(
                                $fieldAlias => $tableAlias . '.' . $fieldName
                        ));
        return $this;
    }

    /**
     * Add column relation to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    protected function addColumnRelationDataToCollection ($collection, $column)
    {
        if (! $collection || ! $column || ! $column->getRelation()) {
            return $this;
        }
        $relation = $column->getRelation();
        $fkFieldName = $relation['fk_field_name'];
        $refFieldName = $relation['ref_field_name'];
        $fieldName = $relation['field_name'];
        $tableName = $relation['table_name'];
        $table = $collection->getTable($tableName);
        $modelValues = array();
        foreach ($collection as $model) {
            $modelValues[$model->getData($fkFieldName)] = array();
        }
        if (count($modelValues)) {
            $adapter = $collection->getConnection();
            $select = $adapter->select()
                ->from($table)
                ->where(
                    $adapter->quoteInto($fkFieldName . ' IN (?)',
                            array_keys($modelValues)));
            $items = $adapter->fetchAll($select);
            foreach ($items as $item) {
                $modelId = $item[$refFieldName];
                $value = $item[$fieldName];
                $modelValues[$modelId][] = $value;
            }
        }
        foreach ($collection as $model) {
            $modelId = $model->getData($fkFieldName);
            if (isset($modelValues[$modelId])) {
                $model->setData($column->getId(), $modelValues[$modelId]);
            }
        }
        return $this;
    }

    /**
     * Get column filter to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    public function addColumnFilterToCollection ($collection, $column)
    {
        $this->addColumnRelationToCollection($collection, $column);
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition = $column->getFilter()->getCondition();
        if ($field && isset($condition)) {
            $collection->addFieldToFilter($field, $condition);
        }
        return $this;
    }

    /**
     * Products Grid
     */
    /**
     * Add column qty data to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    protected function addColumnQtyDataToCollection ($collection, $column, $store)
    {
        if ($store != 0) {
            $websiteId = Mage::getModel("core/store")->load($store)->getWebsiteId();
            $websites = array();
            $websites[] = $websiteId;
        }
        
        $helper = $this->getHelper();
        $stockIds = $helper->getStockIds();
        // Mage::log($stockIds,Zend_Log::DEBUG,'abc',true);
        $qtys = array();
        foreach ($collection as $product) {
            $productId = (int) $product->getId();
            $qtys[$productId] = array();
        }
        if (! empty($qtys)) {
            $adapter = $collection->getConnection();
            $table = $collection->getTable('cataloginventory/stock_item');
            $select = $adapter->select()
                ->from($table)
                ->where(
                    $adapter->quoteInto('product_id IN (?)', array_keys($qtys)));
            $data = $adapter->fetchAll($select);
            foreach ($data as $row) {
                $productId = (int) $row['product_id'];
                $stockId = (int) $row['stock_id'];
                $qty = (float) $row['qty'];
                $qtys[$productId][$stockId] = $qty;
            }
            foreach ($qtys as $productId => $productQtys) {
                foreach ($stockIds as $stockId) {
                    $product = Mage::getModel("catalog/product")->load(
                            $productId);
                    
                    if ($store == 0) {
                        $websites = $product->getWebsiteIds();
                    }
                    // Mage::log($websites,Zend_Log::DEBUG,'abc',true);
                    $websiteId = Mage::getModel("core/website")->load($stockId,
                            'stock_id')->getWebsiteId();
                    if (in_array($websiteId, $websites)) {
                        if (! isset($productQtys[$stockId])) {
                            $qtys[$productId][$stockId] = 0;
                        }
                    } else {
                        unset($qtys[$productId][$stockId]);
                    }
                }
            }
        }
        foreach ($collection as $product) {
            $productId = (int) $product->getId();
            if (isset($qtys[$productId])) {
                $product->setData($column->getId(), $qtys[$productId]);
            }
        }
        return $this;
    }

    /**
     * Add column batch price data to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    protected function addColumnBatchPriceDataToCollection ($collection, $column,
            $store)
    {
        if ($store != 0) {
            $websiteId = Mage::getModel("core/store")->load($store)->getWebsiteId();
            $websites = array();
            $websites[] = $websiteId;
        }
        
        $helper = $this->getHelper();
        // $priceHelper = $helper->getProductPriceHelper();
        $stockIds = $helper->getStockIds();
        
        $storeIds = $helper->getStoreIdsByUsingStockIds();
        foreach ($collection as $product) {
            $batchPrices = array();
            if ($store == 0) {
                $websites = $product->getWebsiteIds();
            }
            foreach ($stockIds as $stockId) {
                $websiteId = Mage::getModel("core/website")->load($stockId,
                        'stock_id')->getWebsiteId();
                if (in_array($websiteId, $websites)) {
                    $_product = Mage::getModel('catalog/product')->setStoreId(
                            $storeIds[$stockId])->load($product->getId());
                    // Mage::log($_product->getData('price'),Zend_Log::DEBUG,'abc',true);
                    $batchPrice = $_product->getData('price');
                    $batchPrices[$stockId] = $batchPrice;
                }
            }
            $product->setBatchPrices($batchPrices);
        }
        return $this;
    }

    /**
     * Get column qty filter to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    public function addColumnQtyFilterToCollection ($collection, $column)
    {
        $helper = $this->getHelper();
        $config = $helper->getConfig();
        if (! $config->isCatalogBackendGridQtyVisible()) {
            return $this;
        }
        $adapter = $collection->getConnection();
        $condition = $column->getFilter()->getCondition();
        $select = $collection->getSelect();
        
        $qtyTableAlias = 'cisi';
        $qtyTable = $collection->getTable('cataloginventory/stock_item');
        $qty = $collection->getConnection()
            ->select()
            ->from(array(
                $qtyTableAlias => $qtyTable
        ), array())
            ->columns(array(
                'qty' => 'SUM(' . $qtyTableAlias . '.qty)'
        ))
            ->where('e.entity_id = ' . $qtyTableAlias . '.product_id')
            ->assemble();
        $conditionPieces = array();
        if (isset($condition['from'])) {
            array_push($conditionPieces,
                    '(' . $qty . ') >= ' . $adapter->quote($condition['from']));
        }
        if (isset($condition['to'])) {
            array_push($conditionPieces,
                    '(' . $qty . ') <= ' . $adapter->quote($condition['to']));
        }
        $select->where(implode(' AND ', $conditionPieces));
        return $this;
    }

    /**
     * Add qty product grid column
     *
     * @param Mage_Adminhtml_Block_Catalog_Product_Grid $grid
     *
     * @return self
     */
    public function addQtyProductGridColumn ($grid)
    {
        $helper = $this->getHelper();
        $config = $helper->getConfig();
        if (! $config->isCatalogBackendGridQtyVisible()) {
            return $this;
        }
        $grid->addColumn('qtys',
                array(
                        'header' => $helper->__('Qty'),
                        'sortable' => false,
                        'index' => 'qtys',
                        'width' => '140px',
                        'align' => 'left',
                        'renderer' => 'allure_catalog/adminhtml_catalog_product_grid_column_renderer_qtys',
                        'filter_condition_callback' => array(
                                $this,
                                'addColumnQtyFilterToCollection'
                        ),
                        'filter' => 'adminhtml/widget_grid_column_filter_range'
                ));
        return $this;
    }

    /**
     * Add batch price product grid column
     *
     * @param Mage_Adminhtml_Block_Catalog_Product_Grid $grid
     *
     * @return self
     */
    public function addBatchPriceProductGridColumn ($grid)
    {
        $helper = $this->getHelper();
        $config = $helper->getConfig();
        if (! $config->isCatalogBackendGridBatchPricesVisible()) {
            return $this;
        }
        $storeId = (int) $grid->getRequest()->getParam('store', 0);
        $store = Mage::app()->getStore($storeId);
        $baseCurrency = $store->getBaseCurrency();
        $grid->addColumn('batch_prices',
                array(
                        'header' => $helper->__('Website Price'),
                        'currency_code' => $baseCurrency->getCode(),
                        'index' => 'batch_prices',
                        'width' => '140px',
                        'align' => 'left',
                        'renderer' => 'allure_catalog/adminhtml_catalog_product_grid_column_renderer_batchprices',
                        'filter' => false,
                        'sortable' => false
                ));
        return $this;
    }

    /**
     * Prepare product grid
     *
     * @param Mage_Adminhtml_Block_Catalog_Product_Grid $grid
     *
     * @return self
     */
    public function prepareProductGrid ($grid, $store)
    {
        $helper = $this->getHelper();
        $config = $helper->getConfig();
        // if ($helper->getVersionHelper()->isGe1600()) {
        $grid->removeColumn('qty');
        // }
        if ($config->isCatalogBackendGridQtyVisible()) {
            $qtyColumnId = 'qtys';
            $this->addColumnQtyDataToCollection($grid->getCollection(),
                    $grid->getColumn($qtyColumnId), $store);
            $grid->addColumnsOrder($qtyColumnId, 'price');
        }
        if ($config->isCatalogBackendGridBatchPricesVisible()) {
            $batchPricesColumnId = 'batch_prices';
            $this->addColumnBatchPriceDataToCollection($grid->getCollection(),
                    $grid->getColumn($batchPricesColumnId), $store);
            $grid->addColumnsOrder($batchPricesColumnId, 'price');
        }
        $grid->sortColumnsByOrder();
        return $this;
    }
}
