<?php
class Allure_MyAccount_IndexController extends Mage_Core_Controller_Front_Action{
	
	public function indexAction() {  
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$this->loadLayout();
			$this->renderLayout();
		}else{
			$this->_redirect('customer/account/');
			return;
		}
		
	}
	
	public function getViewUrl($order)
	{
		return Mage::getUrl('sales/order/view', array('order_id' => $order->getId()));
	}
	
	public function getReorderUrl($order)
	{
		return  Mage::getUrl('sales/order/reorder', array('order_id' => $order->getId()));
	}
	
	public function getOrderHistoryAction(){
		$request = $this->getRequest()->getPost();
		$pageNo=1;
		$limit = 10;
		
		$store = "all";
		$sortOrder = "desc";
		
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
		
		$orders = Mage::getResourceModel('sales/order_collection')
			->addFieldToSelect('*')
			->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
			->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
			;//->setOrder('created_at', 'desc');
		
		if(!empty($store)){
			if($store!='all')
				$orders->addFieldToFilter('main_table.store_id',$store);
		}
			
		if(!empty($sortOrder)){
			$orders->setOrder('main_table.created_at', $sortOrder);
		}
		
		$orders->setCurPage($pageNo);
		$orders->setPageSize($limit);
		$_odd = ''; 
        $i = 0 ;
        $html = '';
        
        $html = '';
        /* foreach ($orders as $_order){
        	$shippingName = "";
        	if($_order->getShippingAddress()!=null){
        		$shippingName = $_order->getShippingAddress()->getName();
        	}else{
        		if($_order->getBillingAddress()!=null)
        			$shippingName = $_order->getBillingAddress()->getName();
        	}
        	
        	$html .= '<tr>';
        	$html .= '<td style="padding-left:10px;"><a>'.$_order->getRealOrderId() .'</a></td>';
        	$html .= '<td class="a-left" style="padding-left:20px;" ><span class="nobr">';
        	$html .= Mage::app()->getLocale()->date(strtotime($_order->getCreatedAtStoreDate()), null, null, false)->toString('MM/dd/yyyy').'</span></td>';
        	$html .= '<td class="a-left" style="padding-left:0;">'.$shippingName.'' .'</td>';
        	$html .= '<td class="a-left" style="padding-left:18px;">'.$_order->formatPrice($_order->getGrandTotal()) .'</td>';
        	$html .= '<td class="a-left" style="padding-left:15px;">'.$_order->getStatusLabel().'</td>';
        	$html .= '<td class="a-left" style="padding-left:10px;">';
        	$html .= '<span class="nobr"><a href="'.$this->getViewUrl($_order) .'">'.$this->__('View Order') .'</a>';
        	if (Mage::helper('sales/reorder')->canReorder($_order)){
        		$html .= '<span>|</span> <a href="'.$this->getReorderUrl($_order) .'" class="link-reorder">'.$this->__('Reorder') .'</a>';
        	}
        	$html .= '</span></td>';
        	$html .= '</tr>';
        } */
        
        
        $this->loadLayout('myaccount_sales_order_history');
        $html = $this->getLayout()->getBlock('sales.order.history')->setOrders($orders)->toHtml(); 
        
       	$count =  $request['count'] ;
       	$count += count($orders);
       	$data = array('html'=>$html,'order_count'=>$count);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
        
	}
	
	
	public function getPurchasedItemsAction(){
		
		$request = $this->getRequest()->getPost();
		$pageNo=1;
		$limit = 10;
		$store = "all";
		$sortOrder = "desc";
		
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
		
		$collection = Mage::getResourceModel('sales/order_item_collection')
		->addAttributeToSelect('*');
		$collection->getSelect()->join( array('orders'=> sales_flat_order),
				'orders.entity_id=main_table.order_id',array('orders.customer_email','orders.customer_id'));
		
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		
		$collection->addFieldToFilter('customer_id',$customer->getId());
		$collection->addFieldToFilter('parent_item_id',array('null' => true));
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
		
		$this->loadLayout('myaccount_purchase_item_load');
		$html = $this->getLayout()->getBlock('purchased_items')
		->setPurchaseOrderCollection($collection)->toHtml();
		$data = array('html'=>$html);
		$jsonData = json_encode(compact('success', 'message', 'data'));
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
	}
	
	
	public function getOpenOrdersAction(){
		$request = $this->getRequest()->getPost();
		$pageNo=1;
		$limit = 10;
		
		$store = "all";
		$sortOrder = "desc";
		
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
				
				$orders = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
				->addFieldToFilter('state', array('in' => array('new','processing')))
				;//->setOrder('created_at', 'desc');
				
				if(!empty($store)){
					if($store!='all')
						$orders->addFieldToFilter('main_table.store_id',$store);
				}
				
				if(!empty($sortOrder)){
					$orders->setOrder('main_table.created_at', $sortOrder);
				}
				
				$orders->setCurPage($pageNo);
				$orders->setPageSize($limit);
				$_odd = '';
				$i = 0 ;
				$html = '';
				
				 $this->loadLayout('myaccount_sales_order_openorders');
				 $html = $this->getLayout()->getBlock('sales.order.openorder')->setOrders($orders)->toHtml();
				 
				 $count =  $request['count'] ;
				 $count += count($orders);
				 $data = array('html'=>$html,'order_count'=>$count);
				 $jsonData = json_encode(compact('success', 'message', 'data'));
				 $this->getResponse()->setHeader('Content-type', 'application/json');
				 $this->getResponse()->setBody($jsonData);
				 
	}
	
}
