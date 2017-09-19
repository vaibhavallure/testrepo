<?php

class Allure_MyAccount_Block_Purchase extends Mage_Core_Block_Template
{
	public function __construct(){
		$request = Mage::app()->getRequest()->getParams();
		$pageNo=1;
		$limit = 10;
		$store = "all";
		$sortOrder = "desc";
		
		if(count($request)){		
			if($request['page'])
				$pageNo=$request['page'];
					
			if($request['limit'])
				$limit = $request['limit'];
						
			if(!empty($request['m_store'])){
				$store = $request['m_store'];
			}
						
			if(!empty($request['m_sort'])){
				$sortOrder = $request['m_sort'];
			}
		}
				
		$collection = Mage::getResourceModel('sales/order_item_collection')
			->addAttributeToSelect('*');
		$collection->getSelect()->join( array('orders'=> sales_flat_order),
				'orders.entity_id=main_table.order_id',array('orders.customer_email','orders.customer_id'));
		
		$collection->getSelect()->join(array(
		    'cat'=>'catalog_product_entity'),
		    'cat.sku=main_table.sku',array()
		    );
		
		$customer = Mage::getSingleton('customer/session')->getCustomer();
				
		$collection->addFieldToFilter('customer_id',$customer->getId());
		$collection->addFieldToFilter('parent_item_id',array('null' => true));
		
		$collection->addFieldToFilter('orders.state',array('in'=>array('complete','processing')));
		//$collection->addFieldToFilter('orders.create_order_method',0);
		
		//$collection->getSelect()->group('main_table.product_id');
				
		if(!empty($store)){
			if($store!='all')
				$collection->addFieldToFilter('main_table.store_id',$store);
		}
				
		if(!empty($sortOrder)){
			$collection->setOrder('main_table.created_at', $sortOrder);
		}
		$collection->setCurPage($pageNo);
		$collection->setPageSize($limit);
		
		$this->setPurchaseOrderCollection($collection);
	}
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$headBlock = $this->getLayout()->getBlock('head');
		if ($headBlock) {
			$headBlock->setTitle($this->__('My Products'));
		}
	}
	
}
