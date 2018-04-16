<?php

class Allure_Inventory_Block_Adminhtml_Purchaseorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
//    protected $_saveParametersInSession = true;

	protected $_massactionBlockName = 'inventory/adminhtml_inventory_edit_tab_mass';
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridLowstock');
        $this->setUseAjax(false);
    }
   
    protected function _prepareCollection()
    {
    	$vendor=Mage::helper('allure_vendor')->getCurrentUserVendor();
        $collection = Mage::getModel('inventory/purchaseorder')->getCollection();
        
       
        
        //$collection->addFieldToFilter('admin_comment', array('like' => '%'.'Demo'.'%'));
      
        if(isset($vendor) && $vendor){
            $collection->addFieldToFilter('status',array('nin' =>Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT));
        	$collection->addFieldToFilter('vendor_id',$vendor);
        
        }
        
        if($_GET['search']!=null){
        	$subCollection = Mage::getModel('inventory/orderitems')->getCollection()->addFieldToSelect('po_id');
        	$subCollection->getSelect()->joinLeft('catalog_product_entity', 'catalog_product_entity.entity_id = main_table.product_id', array('sku'));
        	$subCollection->addFieldToFilter(
        			array('admin_comment', 'vendor_comment','ref_no','sku','vendor_sku'),
        			array(
        					array('like'=>'%'.$_GET['search'].'%'),
        					array('like'=>'%'.$_GET['search'].'%'),
        					array('like'=>'%'.$_GET['search'].'%'),
        					array('like'=>'%'.$_GET['search'].'%'),
        			        array('like'=>'%'.$_GET['search'].'%')
        			)
        			);
        	$subCollection->getSelect()->group('main_table.po_id');
        	$ids = array();
        	foreach ($subCollection as $item) {
        		$ids[] = $item->getPoId();
        	}
        	$collection->addFieldToFilter('po_id', array('in' => $ids));
        	
        	}
        	 $collection->setOrder('po_id', 'DESC');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
    	
    	$this->addColumn('po_id', array(
    			'header'    =>Mage::helper('reports')->__('Id'),
    			'sortable'  =>True,
    			'index'     =>'po_id',
    			'filter'    =>'adminhtml/widget_grid_column_filter_range',
    	));
    	
    	if(!Mage::helper('allure_vendor')->isUserVendor())
    	{
	    	$this->addColumn('vendor_name', array(
	    			'header'    => Mage::helper('reports')->__('Vendor Name'),
	    			'align'     =>'left',
	    	        'index'     => 'vendor_name',
	    	        'renderer'     => 'inventory/adminhtml_purchaseorder_renderer_vendor'
	    	));
    	}
    	$this->addColumn('ref_no', array(
    			'header'    => Mage::helper('reports')->__('Reference  No'),
    			'align'     =>'left',
    			'index'     => 'ref_no',
    	));
    	
    	/* $this->addColumn('items_ordered', array(
    			'header'    =>Mage::helper('reports')->__('Items Ordered'),
    			'sortable'  =>false,
    			'index'     =>'po_id',
    			'renderer'  => 'inventory/adminhtml_purchaseorder_renderer_items'
    	)); */
    	
    	
    	if(!Mage::helper('allure_vendor')->isUserVendor())
    	{
        
        $this->addColumn('total_amount', array(
        		'header'    =>Mage::helper('reports')->__('Amount(USD)'),
        		'sortable'  =>True,
        		'index'     =>'total_amount'
        ));
    	}
        $this->addColumn('stock_id', array(
        		'header'    =>Mage::helper('reports')->__('Store'),
        		'sortable'  =>True,
        		'index'     =>'stock_id',
        		'type'      => 'options',
        		'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
        
        ));
      
        
        $this->addColumn('created_date', array(
        		'header'    =>Mage::helper('reports')->__('Date'),
        		'sortable'  =>True,
        		'index'     =>'created_date',
        		"type" =>   "datetime",
        ));
        
       /*  $this->addColumn('updated_date', array(
        		'header'    =>Mage::helper('reports')->__('Updated Date'),
        		'sortable'  =>True,
        		'index'     =>'updated_date',
        		"type" =>   "datetime",
        )); */
        
        $this->addColumn('status', array(
        		'header'    => Mage::helper('reports')->__('Status'),
        		'align'     => 'left',
        		'width'     => '150px',
        		'index'     => 'status',
        		'type'      => 'options',
        		'options'   => Mage::helper('inventory')->getOrderStatusArray(),
        ));
        
       /* 
        $this->addColumn('action',
        		array(
        				'header'=> Mage::helper('catalog')->__('Action'),
        				'index' => 'po_id',
        		        'is_system' => true,
        				'renderer'  => 'Allure_Inventory_Block_Adminhtml_Purchaseorder_Renderer_Action',// THIS IS WHAT THIS POST IS ALL ABOUT
        		));
        */
        
       
        $this->addColumn('export',
        array(
        'header'=> Mage::helper('catalog')->__('Export'),
        'index' => 'po_id',
        'is_system' => true,
        'renderer'  => 'Allure_Inventory_Block_Adminhtml_Purchaseorder_Renderer_Export',// THIS IS WHAT THIS POST IS ALL ABOUT
        ));
       
        $this->addColumn('lastupdatedby', array(
            'header'    => Mage::helper('reports')->__('Last Updated By'),
            'align'     =>'left',
            'width'     => '300px',
            'index'     => 'lastupdatedby',
            'renderer'     => 'inventory/adminhtml_purchaseorder_renderer_updatedby'
        ));
        
        $this->addExportType('*/*/exportDownloadsCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportDownloadsExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {    
        if(Mage::helper('allure_vendor')->isUserVendor())
        {
            return $this->getUrl('*/*/vendorview', array('id' => $row->getId()));
        }else{
            return $this->getUrl('*/*/view', array('id' => $row->getId()));
        }
    }
    
    protected function _prepareMassaction()
    {
    
    	$this->setMassactionIdField('po_id');
    	$this->getMassactionBlock()->setFormFieldName('po_id');
    	$this->getMassactionBlock()->setUseSelectAll(false);
    	
    	if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
    		$this->getMassactionBlock()->addItem('cancel_order', array(
    				'label'=> Mage::helper('sales')->__('Cancel'),
    				'url'  => $this->getUrl('*/*/massCancel'),
    				'confirm' => Mage::helper('sales')->__('Are you sure?')
    		));
    		
    		$this->getMassactionBlock()->addItem('approve_order', array(
    		    'label'=> Mage::helper('sales')->__('Approve'),
    		    'url'  => $this->getUrl('*/*/massApprove'),
    		    'confirm' => Mage::helper('sales')->__('Are you sure?')
    		));
    	}
    	
    	return $this;
    }
    
    
}



