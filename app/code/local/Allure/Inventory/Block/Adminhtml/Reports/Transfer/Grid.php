<?php

class Allure_Inventory_Block_Adminhtml_Reports_Transfer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        
    }
   
    protected function _prepareCollection()
    {
        

        $entityTypeId = Mage::getModel('eav/entity')
        ->setType('catalog_product')
        ->getTypeId();
        $prodNameAttrId = Mage::getModel('eav/entity_attribute')
        ->loadByCode($entityTypeId, 'name')
        ->getAttributeId();
        
        $collection = Mage::getModel('inventory/transfer')->getCollection();
        $collection->getSelect()->join('admin_user', 'main_table.user_id = admin_user.user_id',array('username'));
        //$collection->getSelect()->joinLeft('catalog_product_entity', 'catalog_product_entity.entity_id = main_table.product_id', array('sku'));
        //$collection->getSelect()->join('catalog_product_entity_varchar', 'main_table.product_id = admin_user.user_id',array('username'));
       
        
        $collection->getSelect()->joinLeft(
        				array('prod' => 'catalog_product_entity'),
        				'prod.entity_id = main_table.product_id',
        				array('sku')
        				)
        				->joinLeft(
        						array('cpev' => 'catalog_product_entity_varchar'),
        						'cpev.entity_id = prod.entity_id AND cpev.attribute_id='.$prodNameAttrId.'',
        						array('name' => 'value')
        						);
        $collection->getSelect()->group('main_table.id');
        $collection->getSelect()->order('main_table.updated_at DESC');
      
        
        
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
    
    	
    	/* 
    	$this->addColumn('image', array(
    			'header'    => Mage::helper('reports')->__('Image'),
    			'align'     =>'left',
    			'index'     => 'image',
    			'renderer'  => 'inventory/adminhtml_inventory_renderer_image'
    	)); */
    	
    	$this->addColumn('sku', array(
    			'header'    =>Mage::helper('reports')->__('Sku'),
    			'sortable'  =>false,
    			'index'     =>'sku'
    	));
    	
       /*  $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'sortable'  =>false,
            'index'     =>'name'
        )); */
        
	
       /*  $this->addColumn('transfer_from', array(
            'header'    =>Mage::helper('reports')->__('From '),
            'sortable'  =>false,
            'index'     =>'transfer_from'
        )); */
        
        $this->addColumn('transfer_from', array(
        		'header'    =>Mage::helper('reports')->__('From'),
        		'sortable'  =>True,
        		'index'     =>'transfer_from',
        		'type'      => 'options',
        		'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
        		
        ));
        
        $this->addColumn('transfer_to', array(
        		'header'    =>Mage::helper('reports')->__('To'),
        		'sortable'  =>True,
        		'index'     =>'transfer_to',
        		'type'      => 'options',
        		'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
        
        ));
        $this->addColumn('qty', array(
        		'header'    =>Mage::helper('reports')->__('Quantity'),
        		'sortable'  =>false,
        		'index'     =>'qty'
        ));
        $this->addColumn('username', array(
        		'header'    =>Mage::helper('reports')->__('Transfered By'),
        		'sortable'  =>false,
        		'index'     =>'username'
        ));
       
        $this->addColumn('updated_at', array(
        		'header'    =>Mage::helper('reports')->__('Updated At'),
        		'sortable'  =>false,
        		'index'     =>'updated_at',
        		'filter_index'=>'main_table.updated_at',
        		"type" =>   "datetime",
        ));
        
     
        $this->addExportType('*/*/exportDownloadsCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportDownloadsExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}
