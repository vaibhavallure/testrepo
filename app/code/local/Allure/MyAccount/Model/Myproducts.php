<?php

class Allure_MyAccount_Model_Myproducts extends Mage_Core_Model_Abstract
{
	public function getOrderList(){
		$request = Mage::app()->getRequest()->getParams();
		$pageNo=1;
		$limit = 10;
		
		$store = "all";
		$sortOrder = "desc";
		if(count($request)>0){
			if(!empty($request['m_store'])){
				$store = $request['m_store'];
			}
			
			if(!empty($request['m_sort'])){
				$sortOrder = $request['m_sort'];
			}
			
			if($request['page'])
				$pageNo=$request['page'];
				
				if($request['limit'])
					$limit = $request['limit'];
		}
		
		$orders = Mage::getResourceModel('sales/order_collection')
		->addFieldToSelect('*')
		->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
		->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
		->setOrder('main_table.created_at', $sortOrder);
		
		if(!empty($store)){
			if($store!='all')
				$orders->addFieldToFilter('main_table.store_id',$store);
		}
		
		
		$orders->setCurPage($pageNo);
		$orders->setPageSize($limit);
		
		Mage::log($orders->getSelect()->__tostring(),Zend_log::DEBUG,'abc',true);die;
		
		$this->setOrders($orders);
	}
}