<?php


class Allure_Inventory_Adminhtml_Inventory_PurchaseController extends Allure_Inventory_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu($this->_menu_path)
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Stock'), Mage::helper('adminhtml')->__('Manage Stock')
        );
        return $this;
    }
   
    public function indexAction() {
    	$this->_initAction();
    	$this->_title($this->__('Inventory'))
    	->_title($this->__('Manage Stock'));
    	
    	$this->renderLayout();
    }
   
    
    public function saveAction() {
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	$data = $this->getRequest()->getPost();
    	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    	foreach ($data['qty'] as $product=>$key){
    		$arr=array_filter($data['qty'][$product]);
    		if(!empty($arr)){
    			foreach ($arr as $stockId=>$qty)
    			{
    				$updateStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$stockId);
    				if(!is_null($updateStock->getItemId()) && ($updateStock->getItemId()!=0)){
    					$previousQty=$updateStock->getQty();
    					$newQty=$updateStock->getQty()+$qty;
    					$resource     = Mage::getSingleton('core/resource');
    					$writeAdapter   = $resource->getConnection('core_write');
    					$table        = $resource->getTableName('cataloginventory/stock_item');
    					$query        = "update {$table} set  qty = '{$newQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
    					$writeAdapter->query($query);
    					
    					$inventory=Mage::getModel('inventory/inventory');
    					$inventory->setProductId($product);
    					$inventory->setUserId($admin->getUserId());
    					$inventory->setPreviousQty($previousQty);
    					$inventory->setAddedQty($qty);
    					$inventory->setUpdatedAt(date("Y-m-d H:i:s"));
    					$inventory->setStockId($stockId);
    					$inventory->save();
    	
    				}
    			}
    		}
    	
    	}
    	Mage::getSingleton('adminhtml/session')->addSuccess("stock updated");
    	$this->_redirect('*/*/');
    }

    public function newAction()
    {
    	$this->loadLayout();
    	$this->_title($this->__('Inventory'))
    	->_title($this->__('Create  Order'));
    	$this->renderLayout();
    }
    public function ordersAction()
    {
    	$this->loadLayout();
    	$this->_title($this->__('Inventory'))
    	->_title($this->__('View Orders'));
    	$this->renderLayout();
    }
    public function viewAction()
    {
    	$this->loadLayout();
    	$this->renderLayout();
    }
   
    public function createOrderAction(){
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	$data = $this->getRequest()->getPost();
    	//$data=json_decode($data['data']);
    	
    	Mage::log($data['data'],Zend_log::DEBUG,'mylogs',true);
    	if($data['data']){
    		$items=array();
    		$vendor=0;
    		foreach ($data['data'] as $key){
    		//	Mage::log('coming',Zend_log::DEBUG,'mylogs',true);
    	   	    //Mage::log('id:'.$key[0]['id'],Zend_log::DEBUG,'mylogs',true);
    			$product = Mage::getModel('catalog/product')->load($key[0]['id']);
    			//Mage::log('Vendor:'.$product->getPrimaryVendor(),Zend_log::DEBUG,'mylogs',true);
    			if($product->getPrimaryVendor())
    				$vendor=$product->getPrimaryVendor();
    			$items[$vendor][$key[0]['id']] = $key[0];
    		}
    		Mage::log($items,Zend_log::DEBUG,'mylogs',true);
    	}
    	$websiteId=1;
    	$stockId=1;
    	if(Mage::getSingleton('core/session')->getMyWebsiteId())
    		$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
    	$website=Mage::getModel( "core/website" )->load($websiteId);
    	$stockId=$website->getStockId();

    	$date = new Zend_Date(Mage::getModel('core/date')->timestamp());
    	$date->addDay('7');
    	$date->toString('Y-m-d H:i:s');
    	$message="";
    	$orderItems="";
    	$notOrderItems="";
    	Mage::log($items,Zend_log::DEBUG,'mylogs',true);
    	if(isset($items)){
    		foreach ($items as $key=>$itemArray){
    			$vendorId=$key;
    			$vendorName=Mage::helper('allure_vendor')->getVanderName($vendorId);
    			$vendorEmail=Mage::helper('allure_vendor')->getVanderEmail($vendorId);
    			Mage::log($vendorEmail,Zend_log::DEBUG,'mylogs',true);
    			if(isset($vendorEmail) && !empty($vendorEmail))
    			{
	    			
	    			$totalAmount=0;
	    			$po_id=null;
	    			foreach ($itemArray as $item)
	    			{
	    				$totalAmount+=$item['qty']*$item['cost'];
	    			}
	    			
	    			Mage::log('Total:'.$totalAmount,Zend_log::DEBUG,'mylogs',true);
	    			$model=Mage::getModel('inventory/purchaseorder');
	    			$orderData = array('ref_no'=>$data['refence_no'],'vendor_id'=>$vendorId,
	    					'created_date'=>date("Y-m-d H:i:s"),
	    					'updated_date'=>date("Y-m-d H:i:s"),
	    					'vendor_name'=>$vendorName,'status'=>'new',
	    					'total_amount'=>$totalAmount,'stock_id'=>$stockId);
	    			
	    			$model->setData($orderData);
	    			$po_id=$model->save()->getId();
	    			
	    			foreach ($itemArray as $item)
	    			{
	    				
	    				$model=Mage::getModel('inventory/orderitems');
	    				$dataItems = array('po_id'=>$po_id,'ref_no'=>$data['refence_no'],'product_id'=>$item['id'],
	    						'requested_qty'=>$item['qty'],
	    						'remaining_qty'=>$item['qty'],
	    						'proposed_qty'=>$item['qty'],'status'=>'new',
	    						'requested_delivery_date'=>$date,
	    						'admin_comment'=>$item['comment'],
	    						'total_amount'=>$item['qty']*$item['cost'],'stock_id'=>$stockId);
	    				Mage::log("Admin Comment:".$item['admin_comment'],Zend_log::DEBUG,'mylogs',true);
	    				$model->setData($dataItems);
	    				$model->save();
	    				
	    				$inven=Mage::getModel('cataloginventory/stock_item')
	    				->loadByProductAndStock($item['id'],$stockId);
	    				$inven->setData('po_sent',1)->save();
	    				$orderItems.=$item['id'].',';
	    			}
	    			
	    			$model=Mage::getModel('inventory/orderlogs');
	    			$logData = array('po_id'=>$po_id,'vendor_id'=>$vendorId,
	    					'user_id'=>$admin->getUserId(),
	    					'date'=>date("Y-m-d H:i:s"),
	    					'total_amount'=>$totalAmount,'stock_id'=>$stockId);
	    			$model->setData($logData);
	    			$model->save()->getId();
	    			Mage::log('Created:'.$po_id,Zend_log::DEBUG,'mylogs',true);
	    			try {
	    				$helper=Mage::helper('inventory');
	    				$helper->sendOrderEmailToVendor($po_id,$orderData,$itemArray,$vendorEmail);
	    			} catch (Exception $e) {
	    			}
	    			
    			}else{
    				
    				foreach ($itemArray as $item)
    				{
    					$notOrderItems.=$item['id'].',';
    				}
    				
    				Mage::log('Please assign vendor to product or vendor email',Zend_log::DEBUG,'mylogs',true);
    			}
    			
    		}
    		
    		if($orderItems && isset($orderItems))
    			$message.="Order Created for items:".$orderItems;
    		if ($notOrderItems && isset($notOrderItems))
    			$message.="Can not create order as vendor or vendor email is not assiged for products:".$notOrderItems;
    		
    		Mage::getSingleton('adminhtml/session')->addSuccess($message);
    		$jsonData = json_encode(compact('success', 'message', 'data'));
    		$this->getResponse()->setHeader('Content-type', 'application/json');
    		$this->getResponse()->setBody($jsonData);
    	}
    	$jsonData = json_encode(compact('success', 'message', 'data'));
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody($jsonData);
    	
    }
    
    
    
    public function saveOrderAction() {
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	$data = $this->getRequest()->getPost();
    	$po_id=$data['order_id'];
    	
    	/* print_r($data);
    	die;
    	 */
    	//get aditional paramters
    	$ship=Mage::app()->getRequest()->getParam('ship');
    	$close=Mage::app()->getRequest()->getParam('close');
    	
    	foreach ($data['order'] as $product=>$key){
    		$arr=array_filter($data['order'][$product]);
    		if(isset($arr) && $arr){
    			
    			$date="";
    			if(isset($arr['proposed_delivery_date']) && $arr['proposed_delivery_date'])
    				$date=date('Y-m-d h:i:s', strtotime($arr['proposed_delivery_date']));
    			
    			$dataItems = array('po_id'=>$po_id,'product_id'=>$product,
    					'requested_qty'=>$arr['requested_qty'],'proposed_qty'=>$arr['proposed_qty'],'status'=>'new',
    					'proposed_delivery_date'=>$date,
    					'admin_comment'=>$arr['admin_comment'],
    					'vendor_comment'=>$arr['vendor_comment']);
    			
    			$sotoreId=Mage::getModel('inventory/purchaseorder')->load($po_id)->getStockId();
    			
    			
    			//Tring to get only one first item and updating it
    			$items=Mage::getModel('inventory/orderitems')->getCollection()->addFieldToFilter('product_id',$product)->addFieldToFilter('po_id',$po_id);
    			foreach ($items as $item){
    				if($date)
    					$item->setData('proposed_delivery_date',$date);
    				$item->setData('admin_comment',$arr['admin_comment']);
    				$item->setData('vendor_comment',$arr['vendor_comment']);
    				$item->setData('requested_qty',$arr['requested_qty']);
    				$item->setData('proposed_qty',$arr['proposed_qty'])->save();
    			}
    			
    			if($date){
    				$days="7";
    				if(Mage::getStoreConfig('allure_vendor/backorder/backorder_time'))
    					$days=Mage::getStoreConfig('allure_vendor/backorder/backorder_time');
    				$backDate=date_create($date);
    				date_add($backDate,date_interval_create_from_date_string($days." days"));
    				$backDate=date_format($backDate,"Y-m-d h:i:s");
    				
    				Mage::getSingleton('catalog/product_action')->updateAttributes(
    						array($product),
    						array('backorder_time'=>$backDate),
    						$storeId
    						);
    	        	}
    		
    	}
    	if($close)
    		$status=Allure_Inventory_Helper_Data::ORDER_STATUS_FULLY_SHIPPED;
    	if($ship)
    		$status=Allure_Inventory_Helper_Data::ORDER_STATUS_PARTIALLY_SHIPPED;
    	$currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
    	$currentDate->toString('Y-m-d H:i:s');
    	$order=Mage::getModel('inventory/purchaseorder')->load($po_id);
    	if(($close || $ship) && isset($status))
	    	$order->setData('status', $status);;
	    $order->setData('updated_date',$currentDate)->save();
    	
    }
	    Mage::getSingleton('adminhtml/session')->addSuccess("Order updated");
	    $this->_redirect('*/*/orders');
	}
	public function receivelistAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	public function viewreceiveAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	public function updatereciveAction(){
		
		$admin = Mage::getSingleton('admin/session')->getUser();
		$data = $this->getRequest()->getPost();
		$po_id=$data['order_id'];
		$currentOrder=Mage::getModel('inventory/purchaseorder')->load($po_id);
		$void=Mage::app()->getRequest()->getParam('void');
		$close=Mage::app()->getRequest()->getParam('close');
		foreach ($data['order'] as $product=>$key){
			$arr=array_filter($data['order'][$product]);
			$updateStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$currentOrder->getStockId());
			if(!empty($arr) && !$void){
				if(!is_null($updateStock->getItemId()) && ($updateStock->getItemId()!=0)){
					
					$sotoreId=Mage::getModel('inventory/purchaseorder')->load($po_id)->getStockId();
					//Tring to get only one first item and updating it
					
					$items=Mage::getModel('inventory/orderitems')->getCollection()->addFieldToFilter('product_id',$product)->addFieldToFilter('po_id',$po_id);
					foreach ($items as $item){
							if($date)
								$item->setData('proposed_delivery_date',$date);
							$remainingQty=$item->getData('remaining_qty')-$arr['proposed_qty'];
							$item->setData('remaining_qty',$remainingQty);
							$item->setData('admin_comment',$arr['admin_comment']);
							$item->setData('vendor_comment',$arr['vendor_comment']);
							$item->setData('requested_qty',$arr['requested_qty']);
							$item->setData('proposed_qty',$arr['proposed_qty'])->save();
					}
					
					//Receive stock
					$previousQty=$updateStock->getQty();
					$newQty=$updateStock->getQty()+$arr['proposed_qty'];
					if($close && isset($close))
						$updateStock->setData('po_sent',0);   //Reset flag on order close
					$updateStock->setData('qty',$newQty)->save();
					
					
					$inventory=Mage::getModel('inventory/inventory');
					$inventory->setProductId($product);
					$inventory->setUserId($admin->getUserId());
					$inventory->setPreviousQty($previousQty);
					$inventory->setAddedQty($arr['proposed_qty']);
					$inventory->setUpdatedAt(date("Y-m-d H:i:s"));
					$inventory->setStockId($currentOrder->getStockId());
					$inventory->setPoId($po_id);
					$inventory->save();
				}
					
			}
			$status=Allure_Inventory_Helper_Data::ORDER_STATUS_PARTIALLY_CLOSED;
			 if($close)
					$status=Allure_Inventory_Helper_Data::ORDER_STATUS_CLOSED;
			 if($void)
					$status=Allure_Inventory_Helper_Data::ORDER_STATUS_REJECT;
			 $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
			 $currentDate->toString('Y-m-d H:i:s');
			 $order=Mage::getModel('inventory/purchaseorder')->load($po_id);
			 if(isset($status))
						$order->setData('status', $status);
			 $order->setData('updated_date',$currentDate)->save();
			 
		}
		Mage::getSingleton('adminhtml/session')->addSuccess("Order Received");
		$this->_redirect('*/*/receivelist');
	}
	public function exportDownloadsCsvAction(){
		$fileName   = 'orders.csv';
		$content    = $this->getLayout()->createBlock('inventory/adminhtml_purchaseorder_grid')
		->setSaveParametersInSession(true)
		->getCsv();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	public function exportDownloadsExcelAction(){
		$fileName   = 'orders.xlsx';
		$content    = $this->getLayout()->createBlock('inventory/adminhtml_purchaseorder_grid')
		->setSaveParametersInSession(true)
		->getExcel($fileName);
		
		$this->_prepareDownloadResponse($fileName, $content);
	}
	public function acceptAction(){
		$id=Mage::app()->getRequest()->getParam('id');
		if($id && isset($id))
		{
			$currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
			$currentDate->toString('Y-m-d H:i:s');
			$status=Allure_Inventory_Helper_Data::ORDER_STATUS_ACCEPT;
			$order=Mage::getModel('inventory/purchaseorder')->load($id);
			if(isset($status))
				$order->setData('status', $status);
			$order->setData('updated_date',$currentDate)->save();
		}
		Mage::getSingleton('adminhtml/session')->addSuccess("Order accepted");
		$this->_redirect('*/*/orders');
	}
}
