<?php

class Allure_Inventory_Block_Adminhtml_Stock_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_isAllWarehouse = true;

    protected $_isEditable = true;

    public function __construct ()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    protected function _getStore ()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _addColumnFilterToCollection ($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                        'catalog/product_website', 'website_id',
                        'product_id=entity_id', null, 'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection ()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');
        
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty', 'cataloginventory/stock_item', 'qty',
                    'product_id=entity_id', '{{table}}.stock_id=1',
                    '{{table}}.stock_id=2', 'left');
        }
        if ($store->getId()) {
            // $collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute('name', 'catalog_product/name',
                    'entity_id', null, 'inner', $adminStore);
            $collection->joinAttribute('custom_name', 'catalog_product/name',
                    'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status',
                    'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility',
                    'catalog_product/visibility', 'entity_id', null, 'inner',
                    $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price',
                    'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status',
                    'entity_id', null, 'inner');
            $collection->joinAttribute('visibility',
                    'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _prepareColumns ()
    {
        $warehouse = Mage::helper('inventoryplus/stock')->getWarehouse();
        if (! $warehouse)
            return parent::_prepareColumns();
        $warehouseId = $warehouse->getId();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        
        $this->addColumn('entity_id',
                array(
                        'header' => Mage::helper('catalog')->__('ID'),
                        'sortable' => true,
                        'width' => '60',
                        'type' => 'number',
                        'index' => 'entity_id'
                ));
        
        $this->addColumn('product_name',
                array(
                        'header' => Mage::helper('catalog')->__('Name'),
                        'align' => 'left',
                        'index' => 'name'
                ));
        
        $this->addColumn('product_sku',
                array(
                        'header' => Mage::helper('catalog')->__('SKU'),
                        'width' => '80px',
                        'index' => 'sku'
                ));
        if (! $this->_isExport) {
            $this->addColumn('product_image',
                    array(
                            'header' => Mage::helper('catalog')->__('Image'),
                            'width' => '90px',
                            'renderer' => 'inventoryplus/adminhtml_renderer_productimage',
                            'index' => 'product_image',
                            'filter' => false
                    ));
        }
        $this->addColumn('product_status',
                array(
                        'header' => Mage::helper('catalog')->__('Status'),
                        'width' => '90px',
                        'index' => 'status',
                        'type' => 'options',
                        'options' => Mage::getSingleton(
                                'catalog/product_status')->getOptionArray()
                ));
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty[1]',
                    array(
                            'header' => Mage::helper('catalog')->__(
                                    'Prevoius Qty'),
                            'width' => '100px',
                            'type' => 'number',
                            'editable' => $this->_isEditable,
                            'index' => 'qty',
                            'default' => 0
                    ));
        }
        
        $this->addColumn('qty_new',
                array(
                        'header' => Mage::helper('catalog')->__('Added Qty'),
                        'width' => '80px',
                        'index' => 'qty',
                        'renderer' => 'allure_catalog/adminhtml_catalog_product_grid_column_renderer_qtys',
                        'editable' => $this->_isEditable,
                        'type' => 'number',
                        'default' => 0
                ));
        
        $this->addExportType('*/*/exportCsv',
                Mage::helper('inventoryplus')->__('CSV'));
        $this->addExportType('*/*/exportXml',
                Mage::helper('inventoryplus')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl ()
    {
        return $this->getUrl('*/*/productsGrid',
                array(
                        '_current' => true
                ));
    }

    public function getRowUrl ($row)
    {
        return false;
    }

    protected function _filterTotalPhysQtyCallback ($collection, $column)
    {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->having(
                    'SUM(warehouse_product.total_qty) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->having(
                    'SUM(warehouse_product.total_qty) <= ?', $filter['to']);
        }
        $filterCollection = clone $collection;
        $filterCollection->clear();
        $filterCollection->setPageSize(false);
        $_stt = 0;
        foreach ($filterCollection as $col) {
            $_stt ++;
        }
        $collection->setSize($_stt);
    }

    public function _filterTotalAvailQtyCallback ($collection, $column)
    {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->having(
                    'SUM(warehouse_product.available_qty) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->having(
                    'SUM(warehouse_product.available_qty) <= ?', $filter['to']);
        }
        $filterCollection = clone $collection;
        $filterCollection->clear();
        $filterCollection->setPageSize(false);
        $_stt = 0;
        foreach ($filterCollection as $col) {
            $_stt ++;
        }
        $collection->setSize($_stt);
    }

    public function _getDisabledProducts ()
    {
        $warehouse = Mage::helper('inventoryplus/stock')->getWarehouse();
        $products = array();
        if (! $warehouse)
            return $products;
        $productCollection = Mage::getResourceModel(
                'inventoryplus/warehouse_product_collection')->addFieldToFilter(
                'warehouse_id', $warehouse->getId());
        if (count($productCollection)) {
            foreach ($productCollection as $product) {
                if ($product->getTotalQty() > 0)
                    $products[$product->getProductId()] = array(
                            'total_qty' => $product->getQty()
                    );
            }
        }
        
        return array_keys($products);
    }

    public function _getSelectedProducts ()
    {
        $productArrays = $this->getProducts();
        $products = '';
        $warehouseProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                Mage::helper('inventoryplus')->parseStr(
                        urldecode($productArray), $warehouseProducts);
                if (count($warehouseProducts)) {
                    foreach ($warehouseProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
        if (! is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }

    public function getSelectedProducts ()
    {
        $warehouse = Mage::helper('inventoryplus/stock')->getWarehouse();
        $products = array();
        if (! $warehouse)
            return $products;
        $productCollection = Mage::getResourceModel(
                'inventoryplus/warehouse_product_collection')->addFieldToFilter(
                'warehouse_id', $warehouse->getId());
        if (count($productCollection)) {
            foreach ($productCollection as $product) {
                $products[$product->getProductId()] = array(
                        'total_qty' => $product->getQty(),
                        'product_location' => $product->getProductLocation()
                );
            }
        }
        return $products;
    }

    public function getWarehouse ()
    {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        if (Mage::helper('core')->isModuleEnabled(
                'Magestore_Inventorywarehouse')) {
            $warehouseId = Mage::getModel('admin/session')->getData(
                    'stock_warehouse_id');
            if ($warehouseId) {
                if (Mage::helper('inventoryplus/warehouse')->canEdit($adminId,
                        $warehouseId))
                    return Mage::getModel('inventoryplus/warehouse')->load(
                            $warehouseId);
            } else {
                $allWarehouseEnable = Mage::helper('inventoryplus/warehouse')->getWarehouseEnable();
                if ($allWarehouseEnable) {
                    foreach ($allWarehouseEnable as $warehouseId) {
                        Mage::getModel('admin/session')->setData(
                                'stock_warehouse_id', $warehouseId);
                        return Mage::getModel('inventoryplus/warehouse')->load(
                                $warehouseId);
                    }
                } else {
                    return false;
                }
            }
        } else {
            return Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem();
        }
        return false;
    }
}
