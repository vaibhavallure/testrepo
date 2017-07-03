<?php

class Allure_MyAccount_Block_Purchase extends Mage_Checkout_Block_Cart
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$headBlock = $this->getLayout()->getBlock('head');
		if ($headBlock) {
			$headBlock->setTitle($this->__('My Products'));
		}
	}
	
	public function getPurchasedItems(){
		//if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			
			$store = 'all';
			if(empty($_GET['m_store']))
				$store = $_GET['m_store'];
			
			$sortOrder = 'asc';
			if(!empty($sortOrder))
				$sortOrder = $_GET['m_sort'];
			
			$collection = Mage::getResourceModel('sales/order_item_collection')
			->addAttributeToSelect('*');
			$collection->getSelect()->join( array('orders'=> sales_flat_order),
					'orders.entity_id=main_table.order_id',array('orders.customer_email','orders.customer_id'));
			
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			
			$collection->addFieldToFilter('customer_id',$customer->getId());
			//$collection->getSelect()->group('main_table.product_id');
			
			if(!empty($store)){
				if($store!='all')
					$collection->addFieldToFilter('main_table.store_id',$store);
			}
			
			if(!empty($sortOrder)){
				$collection->setOrder('main_table.created_at', $sortOrder);
			}
			
			$collection->setCurPage(1);
			$collection->setPageSize(5);
			
			return $collection;
		//}
	}
	
	public function getItemHtml($item)
	{
		$renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
		return $renderer->toHtml();
	}
}
