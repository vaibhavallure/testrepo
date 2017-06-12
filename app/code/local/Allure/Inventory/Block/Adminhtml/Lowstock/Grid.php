<?php

class Allure_Inventory_Block_Adminhtml_Lowstock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
     
    }
   
    protected function _prepareCollection()
    {
        
        $websiteId=1;
        if(Mage::getSingleton('core/session')->getMyWebsiteId())
        	$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
        $website=Mage::getModel( "core/website" )->load($websiteId);
        $storeId=$website->getStoreId();
        $stockId=$website->getStockId();
        
        $category=Mage::getModel('catalog/category')->load(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
        ->addAttributeToSelect('*')
        ->setStoreId($storeId)
        ->joinInventoryItem('qty')
        ->joinInventoryItem('stock_id')
        ->useManageStockFilter($storeId)
        ->useNotifyStockQtyFilter($storeId)
        ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);
        $collection->addAttributeToFilter('stock_id', array('eq' => $stockId));
        $collection->addCategoryFilter($category);
        if( $storeId ) {
        	$collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
    	
    	$this->addColumn('entity_id', array(
    			'header'    =>Mage::helper('reports')->__('Id'),
    			'sortable'  =>false,
    			'index'     =>'entity_id',
    			'filter'    =>'adminhtml/widget_grid_column_filter_range',
    	));
    	
    
        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'sortable'  =>false,
            'index'     =>'name'
        ));
	
        $this->addColumn('sku', array(
            'header'    =>Mage::helper('reports')->__('Product SKU'),
            'sortable'  =>false,
            'index'     =>'sku'
        ));
        
        $this->addColumn('qty', array(
        		'header'    =>Mage::helper('reports')->__('Qty'),
        		'sortable'  =>false,
        		'index'     =>'qty'
        ));

    
        $this->addExportType('*/*/exportLowStcokExcel', Mage::helper('reports')->__('Excel'));
        return parent::_prepareColumns();
    }
}
