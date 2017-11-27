<?php
class Allure_MyAccount_IndexController extends Mage_Core_Controller_Front_Action{
	
	public function indexAction() {  
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			
			if( $this->_methodIsValid() !== true ) {
				$methods = Mage::helper('tokenbase')->getActiveMethods();
				
				if( count( $methods ) > 0 ) {
					sort( $methods );
					
					Mage::register( 'tokenbase_method', $methods[0] );
				}
				else {
					Mage::register( 'tokenbase_method', 'authnetcim' );
				}
			}
			
			$this->loadLayout();
			$this->renderLayout();
		}else{
			$this->_redirect('customer/account/');
			return;
		}
		
	}
	
	protected function _methodIsValid()
	{
		$method	= $this->getRequest()->getParam('method');
		
		if( in_array( $method, Mage::helper('tokenbase')->getActiveMethods() ) !== false ) {
			Mage::register( 'tokenbase_method', $method );
			
			return true;
		}
		
		return false;
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
        
		$helper    = Mage::helper("myaccount");
		$AllOrder  = $helper::ALL_ORDER;
		$orders    = $helper->getOrdersHistory($AllOrder);
        
        $this->loadLayout('myaccount_sales_order_history');
        $html = $this->getLayout()->getBlock('sales.order.history')
                    ->setOrders($orders)
                    ->toHtml(); 
        
       	$count =  $request['count'] ;
       	$count += count($orders);
       	$data = array('html'=>$html,'order_count'=>$count);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
        
	}
	
	
	public function getPurchasedItemsAction(){
	    $helper     = Mage::helper("myaccount");
	    $collection = $helper->getPurchaseItems();
	    
		$this->loadLayout('myaccount_purchase_item_load');
		$html = $this->getLayout()->getBlock('purchased_items')
		              ->setPurchaseOrderCollection($collection)
		              ->toHtml();
		$data = array('html'=>$html);
		$jsonData = json_encode(compact('success', 'message', 'data'));
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
	}
	
	
	public function getOpenOrdersAction(){
		$request = $this->getRequest()->getPost();
		
		$helper    = Mage::helper("myaccount");
		$openOrder = $helper::OPEN_ORDER;
		$orders    = $helper->getOrdersHistory($openOrder);
				
	    $this->loadLayout('myaccount_sales_order_openorders');
		$html = $this->getLayout()->getBlock('sales.order.openorder')->setOrders($orders)->toHtml();
				 
		$count =  $request['count'] ;
		$count += count($orders);
		$data = array('html'=>$html,'order_count'=>$count);
		$jsonData = json_encode(compact('success', 'message', 'data'));
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
				 
	}
	
	public function trackAction()
	{
		$shippingInfoModel = Mage::getModel('shipping/info')->loadByHash($this->getRequest()->getParam('hash'));
		Mage::register('current_shipping_info', $shippingInfoModel);
		if (count($shippingInfoModel->getTrackingInfo()) == 0) {
			$this->norouteAction();
			return;
		}
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function loadOrderViewAction(){
	    $request   = $this->getRequest()->getPost();
	    $order     = Mage::getModel('sales/order')->load($request['order_id']);
	    $orderType = $request['order_type'];
	    Mage::register('current_order', $order);
	    
	    $isInvoice      = false;
	    $isShipment     = false;
	    $isCreditmemo   = false;
	    
	    if ($order->hasInvoices()) {
	        $isInvoice = true;
	    }
	    if ($order->hasShipments()) {
	        $isShipment = true;
	    }
	    if ($order->hasCreditmemos()) {
	        $isCreditmemo = true;
	    }
	    
	    $this->loadLayout('myaccount_sales_order_view');
	    $block = $this->getLayout()->getBlock('myaccount.order_view');
	    $html  = $block->setOrderType($orderType)
	               ->setOrderId($order->getId())
	               ->setIsInvoice($isInvoice)
	               ->setIsShipment($isShipment)
	               ->setIsCreditmemo($isCreditmemo) 
	               ->toHtml();
	    
	    $data = array('html'=>$html);
	    $jsonData = json_encode(compact('success', 'message', 'data'));
	    $this->getResponse()->setHeader('Content-type', 'application/json');
	    $this->getResponse()->setBody($jsonData);
	}
	
}
