<?php

class Allure_Noimages_Block_Adminhtml_Noimages_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridLowstock');
        $this->setUseAjax(false);
    }
   
    protected function _prepareCollection()
    {
    	$collection=Mage::getModel('catalog/product')
    	->getCollection()
    	->addAttributeToSelect('*')
    	->addAttributeToFilter(array(
    			array (
    					'attribute' => 'image',
    					'like' => 'no_selection'
    			),
    			array (
    					'attribute' => 'image', // null fields
    					'null' => true
    			),
    			array (
    					'attribute' => 'image', // empty, but not null
    					'eq' => ''
    			),
    			array (
    					'attribute' => 'image', // check for information that doesn't conform to Magento's formatting
    					'nlike' => '%/%/%'
    			),
    	));
    /* $collection	->addAttributeToFilter('status', array('eq' => 1))
    	->addAttributeToFilter('type_id', array('eq' => 'configurable')); */
    	//->addAttributeToFilter('visibility', array(
    	//		'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
    	$collection->getSelect()->group('e.entity_id');
    	$collection->setOrder('sku','ASC');
    	/* echo $collection->getSelect();
    	die; */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    
/*     public function getCustomCollection(){
    	$collection=Mage::getModel('catalog/product')
    		->getCollection()
    		->addAttributeToSelect('*')
    		->addAttributeToFilter('status', array('eq' => 1))
    		->addAttributeToFilter('type_id', array('eq' => 'configurable'));
    		//->addAttributeToFilter('visibility', array(
    		//	'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
    	$collection->getSelect()->group('e.entity_id');
    	$collection->setOrder('sku','ASC');
    	return $collection;
    }
     */
    
    protected function _prepareColumns()
    {
    	
    	$this->addColumn('entity_id', array(
    			'header'    =>Mage::helper('reports')->__('Id'),
    			'sortable'  =>True,
    			'index'     =>'entity_id',
    			'filter'    =>'adminhtml/widget_grid_column_filter_range',
    	));
    	
    	/* $this->addColumn('thumb',
    		array(
    				'header'    => Mage::helper('catalog')->__('Thumbnail'),
    				'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
    				'index'		=> 'thumbnail',
    				'sortable'  => true,
    				'filter'    => false,
    				'width'     => 90,
    	)); */
    	
    	$this->addColumn('type',
    			array(
    					'header'=> Mage::helper('catalog')->__('Type'),
    					'width' => '60px',
    					'index' => 'type_id',
    					'type'  => 'options',
    					'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
    	));
    
    	$this->addColumn('sku', array(
    			'header'    => Mage::helper('reports')->__('SKU'),
    			'align'     =>'left',
    			'index'     => 'sku',
    	));
    	
    	$this->addColumn('name', array(
    			'header'    =>Mage::helper('reports')->__('Name'),
    			'sortable'  =>false,
    			'index'     =>'name'
    	));
       
    	/* $this->addColumn('url_key', array(
    			'header'    =>Mage::helper('reports')->__('View In Front'),
    			'sortable'  =>false,
    			'index'     =>'url_key',
    			'renderer'  => 'noimages/adminhtml_noimages_grid_url',
    	)); */
    	
    	/* $this->addColumn('action',array(
    			'header'    => Mage::helper('sales')->__('View In Admin'),
    			'width'     => '5%',
    			'type'      => 'action',
    			'getter'     => 'getId',
    			'actions'   => array(
    					array(
    							'caption' => Mage::helper('sales')->__('View'),
    							'url'     => array('base'=>'adminhtml/catalog_product/edit'),
    							'field'   => 'id',
    							'popup'   => true
    					)
    			),
    			'filter'    => false,
    			'sortable'  => false,
    			'is_system' => true,
    		)
    	); */
    	
        $this->addExportType('*/*/exportCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
   
    
}
