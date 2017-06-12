<?php

class Allure_Inventory_Block_Adminhtml_Reports_Lowstock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	protected function _prepareCollection()
    {
        
       /*  $websiteId=1;
        if(Mage::getSingleton('core/session')->getMyWebsiteId())
        	$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
        $website=Mage::getModel( "core/website" )->load($websiteId);
        $storeId=$website->getStoreId();
        $stockId=$website->getStockId();
        
        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
        ->addAttributeToSelect('*')
        ->setStoreId($storeId)
        ->joinInventoryItem('qty')
        ->joinInventoryItem('stock_id')
        ->useManageStockFilter($storeId)
        ->useNotifyStockQtyFilter($storeId)
        ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);
        $collection->addAttributeToFilter('stock_id', array('eq' => $stockId));
        $collection->addAttributeToFilter('type_id', 'simple');
        if( $storeId ) {
        	$collection->addStoreFilter($storeId);
        } */
        
        $collection=Mage::getModel('inventory/lowstock')->getCollection();
        $collection->getSelect()->group('main_table.id');
        $collection->getSelect()->order('main_table.id DESC');
        
       /* echo  $collection->getSelect();
       die; */
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
    	
    	$this->addColumn('id', array(
    			'header'    =>Mage::helper('reports')->__('Id'),
    			'sortable'  =>false,
    			'index'     =>'id',
    			'filter'    =>'adminhtml/widget_grid_column_filter_range',
    	));
    	
    
        $this->addColumn('sent_to', array(
            'header'    =>Mage::helper('reports')->__('Sent To'),
            'sortable'  =>false,
            'index'     =>'sent_to'
        ));
        
        
        $this->addColumn('store_id', array(
        		'header'    =>Mage::helper('reports')->__('Store'),
        		'sortable'  =>True,
        		'index'     =>'store_id',
        		'type'      => 'options',
        		'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
        
        ));
        
        $this->addColumn('created_date', array(
        		'header'    =>Mage::helper('reports')->__('Date'),
        		'sortable'  =>True,
        		'index'     =>'created_date',
        		"type" =>   "datetime",
        ));
        
        
        $this->addColumn('path', array(
            'header'    =>Mage::helper('reports')->__('Download'),
            'sortable'  =>false,
            'index'     =>'path',
            'renderer'  => 'inventory/adminhtml_reports_lowstock_renderer_download'
        ));
        
        
        return parent::_prepareColumns();
    }
}
