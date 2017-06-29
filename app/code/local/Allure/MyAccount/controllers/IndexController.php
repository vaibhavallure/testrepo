<?php
class Allure_MyAccount_IndexController extends Mage_Core_Controller_Front_Action{
	public function indexAction() {  
		$this->loadLayout();  
		$this->renderLayout();
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
		if($request['page'])
			$pageNo=$request['page'];
		
		if($request['limit'])
			$limit = $request['limit'];
		
		$orders = Mage::getResourceModel('sales/order_collection')
			->addFieldToSelect('*')
			->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
			->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
			->setOrder('created_at', 'desc');
		
		$orders->setCurPage($pageNo);
		$orders->setPageSize($limit);
		$_odd = ''; 
        $i = 0 ;
        $html = '';
        foreach ($orders as $_order){
         	$html .= '<tr>';
         	$html .= '<td style="padding-left:10px;"><a href="'.$this->getViewUrl($_order) .'">'.$_order->getRealOrderId() .'</a></td>';
         	$html .= '<td class="a-left" style="padding-left:20px;" ><span class="nobr">';
         	$html .= Mage::app()->getLocale()->date(strtotime($_order->getCreatedAtStoreDate()), null, null, false)->toString('MM/dd/yyyy').'</span></td>';
         	$html .= '<td class="a-left" style="padding-left:0;">'.$_order->getShippingAddress()->getName().'' .'</td>';
         	$html .= '<td class="a-left" style="padding-left:18px;">'.$_order->formatPrice($_order->getGrandTotal()) .'</td>';
         	$html .= '<td class="a-left" style="padding-left:15px;">'.$_order->getStatusLabel().'</td>';
         	$html .= '<td class="a-left" style="padding-left:10px;">';
         	$html .= '<span class="nobr"><a href="'.$this->getViewUrl($_order) .'">'.$this->__('View Order') .'</a>';
            if (Mage::helper('sales/reorder')->canReorder($_order)){
           		$html .= '<span>|</span> <a href="'.$this->getReorderUrl($_order) .'" class="link-reorder">'.$this->__('Reorder') .'</a>';
            }
            $html .= '</span></td>';
            $html .= '</tr>';
        }
        
        $data = array('html'=>$html);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
        
	}
	
	
	public function getPurchasedItemsAction(){
		$request = $this->getRequest()->getPost();
		$pageNo=1;
		$limit = 10;
		if($request['page'])
			$pageNo=$request['page'];
			
		if($request['limit'])
			$limit = $request['limit'];
		
		
		$collection = Mage::getResourceModel('sales/order_item_collection')
			->addAttributeToSelect('*');
		$collection->getSelect()->join( array('orders'=> sales_flat_order),
					'orders.entity_id=main_table.order_id',array('orders.customer_email','orders.customer_id'));
			
		$customer = Mage::getSingleton('customer/session')->getCustomer();
			
		$collection->addFieldToFilter('customer_id',$customer->getId());
		//$collection->getSelect()->group('main_table.product_id');
			
		$collection->setCurPage($pageNo);
		$collection->setPageSize($limit);
		
		$storeList = array();
		$stores = Mage::app()->getStores();
		foreach ($stores as $store) {
			$storeList[$store->getId()] = $store->getName();
		}
		
		$html = '';
		foreach ($collection as $_item){
			$product = $_item->getProduct();
			$arrayOfParentIds = Mage::getSingleton('catalog/product_type_configurable')->getParentIdsByChild($_item->getProduct()->getId());
			$parentId = (count($arrayOfParentIds) > 0 ? $arrayOfParentIds[0] : null);
			$url = $_item->getProduct()->getProductUrl();
			if(!is_null($parentId)){
				$url = Mage::getModel("catalog/product")->load($parentId)->getProductUrl();
			}
			
			
			$productName = $product->getName();
			$productNotAvailableClass = "";
			$productDescr = $_item->getProduct()->getDescription();
			if(!$product->getId()){
				$productName = $_item->getName();
				$productNotAvailableClass = "current-item-not-available";
				$productDescr = $_item->getDescription();
			}
			
			
			
			$receiptUrl = Mage::getUrl("sales/order/print")."order_id/".$_item->getOrderId()."/";
			$reorderUrl = Mage::getUrl("sales/order/reorder")."order_id/".$_item->getOrderId()."/";
			$html .= '<tr data-id="'.$_item->getId().'" class="'.$productNotAvailableClass.'">';
			$html .= '<td class="cart_col1">';
			$html .= '<a href="'.$url.'" title="'.$productName.'" class="product-image">'.
					'<img src="'.Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(74,96).'" width="74" height="96" alt="'.$productName.'">'.
					'</a>'.
					'<a data-img="'.Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(350,350).'" class="mt-piercing-photo" href="javascript:void(0);">View Piercing Photo</a>'.
					'</td>';
			
			$html .= '<td class="cart_col2">';
			$html .= '<h2 class="product-name">';
			$html .=  '<a href="'.$url.'">'.$productName.'</a></h2>'.
					'<span class="mt-purchase-added-at">Purchased: '.date('M d,Y H:i a',strtotime($_item->getCreatedAt())).'</span>'.
					'<button class="button">'.$storeList[$_item->getStoreId()].'</button></td>';
			
			$html .= '<td class="cart_col4">';
			$html .=  '<div class="qty-wrap">';
			$html .=  '<span>'.number_format($_item->getQtyOrdered()).'</span>'.
					  '</div></td>';
			
			$html .= '<td class="cart_col3">';
			$html .= '<span class="price_multi"></span> <span class="price">'.Mage::helper('checkout')->formatPrice($_item->getPrice()).'</span></td>';
			
			
			$html .= '<td class="cart_col6">';
			$html .= '<div class="mt-purchase-btn">';
			$html .= '<button data-url="'.$receiptUrl.'" class="button" onclick="openPurchaseWindow(this)">See Receipts</button>';
			$html .= '</div>';
			if($product->getId()){
				$html .= '<div class="mt-purchase-btn">';
				$html .= '<button data-url="'.$reorderUrl.'" class="button" onclick="openPurchaseWindow(this)">Reorder</button>';
				$html .= '</div>';
				$html .= '<div class="mt-purchase-btn">';
				$html .= '<button class="button">Share</button>';
				$html .= '</div>';
			}
			$html .= '</td>';
			$html .= '</tr>';
		}
		$data = array('html'=>$html);
		$jsonData = json_encode(compact('success', 'message', 'data'));
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($jsonData);
	}
}
