<?php
class Allure_Inventory_Model_Cron {
	public function autoProcessLowstockReports(){
		$stores= Mage::getStoreConfig('inventory/general/enable_stores');
		$stores= explode(",",$stores);
		$websiteIds=array();
		if (!empty($stores)){
		    foreach ($stores as $id){
		        $website=Mage::getModel('core/store')->load($id);
		        array_push($websiteIds, $website->getWebsiteId());
		        
		    }
		}
		$websiteIds=array_unique($websiteIds);
		if(!empty($websiteIds)){
		    foreach ($websiteIds as $websiteId) {
		        $this->writeData($websiteId);
		    }
		}
	}

	public function writeData($websiteId){
		try {
		    $helper = Mage::helper("inventory");
		    $childCategoryId = $helper->getChildCategoryId();
		    if(empty($childCategoryId)){
		        $childCategoryId = $helper->getParentCategoryId();
		    }
		    
			$path = Mage::getBaseDir('var') . DS . 'export' . DS;
			
			$website=Mage::getModel( "core/website" )->load($websiteId);
			$date = Mage::getModel('core/date')->date('Y_m_d');
			$websiteName=str_replace("/", "-",$website->getName());
			$name   = 'lowstock_'.$websiteName.$date.'.csv';
			$file_path = Mage::getBaseDir('var') . DS . 'export' . DS;
			$storeId=$website->getStoreId();
			$stockId=$website->getStockId();
			
			
			$io = new Varien_Io_File();
			$path = Mage::getBaseDir('var') . DS . 'export' . DS;
			$file = $path . DS . $name;
			$io->setAllowCreateFolders(true);
			$io->open(array('path' => $path));
			$io->streamOpen($file, 'w+');
			$io->streamLock(true);
			$header = array("Id","sku","Qty");
			$io->streamWriteCsv($header);
			
			$collection = Mage::getModel('inventory/orderitems')->getCollection();
			$collection->getSelect()->joinLeft('allure_purchase_order', 'allure_purchase_order.po_id = main_table.po_id');
			$collection->addFieldToFilter('allure_purchase_order.status',array('nin' =>array( 'closed','cancel')));
			$collection->addFieldToFilter('main_table.is_custom',0);
			$collection->addFieldToFilter('allure_purchase_order.stock_id',$stockId);
			
			$productArray=array();
			foreach ($collection as $Poproducts){
			    $productArray[]=$Poproducts->getProductId();
			    
			}
			
			
			/* $subCollection=Mage::getModel('catalog/product')->getUsedCategoryProductCollection(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
			$subCollection->addAttributeToSelect('entity_id')->setStoreId($storeId);
			$subCollection->getSelect()->group('e.entity_id'); */
			
			$subCollection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToFilter('type_id', array('eq' => 'simple'));
			$subCollection->addAttributeToSelect('*')->setStoreId($storeId);
			$subCollection->getSelect()->join(
			    array('category_product' => 'catalog_category_product'),
			    'category_product.product_id = e.entity_id',
			    array('category_id')
			    );
			
			$subCollection->getSelect()->where('category_product.category_id = '.$childCategoryId);
			$subCollection->getSelect()->group('e.entity_id');
			
			$ids=array();
			foreach ($subCollection as  $product){
				$ids[]=$product->getId();
			}
			
			
			$collection = Mage::getResourceModel('reports/product_lowstock_collection')
			->addAttributeToSelect('*')
			->setStoreId($storeId)
			->joinInventoryItem('qty')
			->joinInventoryItem('stock_id')
			->useManageStockFilter($storeId)
			->useNotifyStockQtyFilter($storeId)
			->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);
			$collection->addAttributeToFilter('stock_id', array('eq' => $stockId));
			//$collection->addCategoryFilter($category);
			$collection->addAttributeToFilter(
					'status',
					array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
			);
			$collection->addAttributeToFilter('type_id', 'simple');
			$collection->addAttributeToFilter('entity_id',array('in' => $ids));
			if(!empty($productArray))
			    $collection->addAttributeToFilter('entity_id',array('nin' => $productArray));
			if( $storeId ) {
				$collection->addStoreFilter($storeId);
			}
			$collection->getSelect()->group('e.entity_id');
			
			
			if(count($collection->getData())> 0 && !empty($collection->getData())){
					
			    
				foreach ($collection->getData() as $product){
						
					$id = $product['entity_id'];
					$sku = $product['sku'];
						
					$qty = $product['qty'];
					$data = array("Id"=>$product['entity_id'],"sku"=>$product['sku'],"Qty"=>$product['qty']);
					$io->streamWriteCsv($data);
					//fputcsv($fp, array($id,$sku,$qty), ",");
				}
				$this->send_email($websiteId);
				$this->createDraftOrder($collection->getData(),$stockId);
				
			}
		} catch (Exception $e) {
		    Mage::log("Exception Occured:".$e->getMessage(),Zend_log::DEBUG,'lowstock',true);
		}
	}
	public function send_email($websiteId) {
		try {
			$path = Mage::getBaseDir('var') . DS . 'export' . DS;
			
			$website=Mage::getModel( "core/website" )->load($websiteId);
			$date = Mage::getModel('core/date')->date('Y_m_d');
			$websiteName=str_replace("/", "-",$website->getName());
			$name   = 'lowstock_'.$websiteName.$date.'.csv';
			$file_path = Mage::getBaseDir('var') . DS . 'export' . DS;
			$file = $path . DS . $name;
			if(Mage::getStoreConfig('inventory/email/enabled')):
			$mailTemplate = Mage::getModel('core/email_template');
			$recipient=Mage::getStoreConfig('inventory/email/recipient_email');
			$recipientArr=explode(",",$recipient);
			$mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name')); // use general Mage::getStoreConfig('trans_email/ident_general/name');
			$mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email')); // use general Mage::getStoreConfig('trans_email/ident_general/email')
			$mailTemplate->setTemplateSubject('Low Stock Report '."- ".$website->getName());
			$mailTemplate->setTemplateText('PFA of low stock for'.' '.$website->getName());
			$mailTemplate->getMail()->createAttachment(
					file_get_contents($file),
					Zend_Mime::TYPE_OCTETSTREAM,
					Zend_Mime::DISPOSITION_ATTACHMENT,
					Zend_Mime::ENCODING_BASE64,
					$name
					);
			try { 
				$mailTemplate->send($recipientArr);
				
			} catch (Exception $e) {
				Mage::log($e,Zend_log::DEBUG,'lowstock',true);
			}
			
				
			$logData = array('sent_to'=>$recipient,
					'store_id'=>$website->getId(),
					'created_date'=>date("Y-m-d H:i:s"),
					'path'=>$file);
			$model=Mage::getModel('inventory/lowstock');
			$model->setData($logData);
			$model->save()->getId();
			endif;
			Mage::log("Email Sent",Zend_log::DEBUG,'lowstock',true);
			
		} catch (Exception $e) {
			Mage::log("Exception Sending mail:".$e,Zend_log::DEBUG,'lowstock',true);
		}
	}
	public function createDraftOrder($data,$stockId){
	    //echo "<pre>";
	  	$vendor = Mage::getStoreConfig("allure_vendor/manage_vendor/vendor");
	    $orderStatus = Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT;
	    $helper = Mage::helper('inventory');
	    $date = new Zend_Date(Mage::getModel('core/date')->timestamp());
	    $date->addDay('7');
	    $date->toString('Y-m-d H:i:s');
	    
	    $items = array();
	    
	    foreach ($data as $item) {
	        $product = Mage::getModel('catalog/product')
	        ->setStoreId($stockId)->load($item['entity_id']);
	        
	        if($product->getId() && !empty($product->getMaxQty())){
    	        if ($product->getPrimaryVendor())
    	            $vendor = $product->getPrimaryVendor();
    	       $tmp=array();
    	       $tmp['item_id']=$product->getId();
    	       $tmp['qty']=$product->getMaxQty();
    	       $tmp['is_custom']=0;
    	       $tmp['vendor_sku']=$product->getVendorItemNo();
    	       $tmp['cost']=$product->getCost()?$product->getCost():0;
    	       $items[$vendor][$product->getId()] = $tmp;
    	       unset($tmp);
    	       
	        }else{
	            Mage::log("product Max Qty option not set for Product:".$item['entity_id'].'-'.$product->getSku().'------store id:'.$stockId,Zend_log::DEBUG,'lowstock_PO_error.log',true);
	        }
	    }
	    if(isset($items) && !empty($items)){
	        
	            foreach ($items as $key => $itemArray) {
	                $vendorId = $key;
	                $vendorName = Mage::helper('allure_vendor')->getVanderName($vendorId);
	                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($vendorId);
	                if (isset($vendorName) && ! empty($vendorName)) {
	                    $totalAmount = 0;
	                    $po_id = null;
	                    foreach ($itemArray as $item) {
	                        $totalAmount += $item['qty'] * $item['cost'];
	                    }
	                    
	                    Mage::log('Total:' . $totalAmount, Zend_log::DEBUG, 'mylogs', true);
	                    $model = Mage::getModel('inventory/purchaseorder');
	                    $orderData = array(
	                                'ref_no' => 'lowstock report',
	                                'vendor_id' => $vendorId,
	                                'created_date' => date("Y-m-d H:i:s"),
	                                'updated_date' => date("Y-m-d H:i:s"),
	                                'vendor_name' => $vendorName,
	                                'status' => $orderStatus,
	                                'total_amount' => $totalAmount,
	                                'stock_id' => $stockId
	                    );
	                            
	                    $model->setData($orderData);
	                    $po_id = $model->save()->getId();
	                            
	                    foreach ($itemArray as $item) {
                        // Map Order items With Order
                        // Insert entry in allure_purchase_order_item
                        
                        Mage::log("temp:", Zend_log::DEBUG, 'mylogs', true);
                        Mage::log($item, Zend_log::DEBUG, 'mylogs', true);
                        $model = Mage::getModel('inventory/orderitems');
                        $dataItems = array(
                            'po_id' => $po_id,
                            'ref_no' => 'lowstock report',
                            'product_id' => $item['item_id'],
                            'requested_qty' => $item['qty'],
                            'remaining_qty' => $item['qty'],
                            'proposed_qty' => $item['qty'],
                            'status' => $orderStatus,
                            'requested_delivery_date' => $date,
                            'is_custom' => $item['is_custom'],
                            'admin_comment' => $item['comment'],
                            'total_amount' => $item['qty'] * $item['cost'],
                            'stock_id' => $stockId,
                            'vendor_sku' => $item['vendor_sku']
                        
                        );
                        $model->setData($dataItems);
                        $model->save();
                        
                        // If Item is Custom dont set PO Sent flag
                            if (! $item['is_custom']) {
                                $inven = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($item['item_id'], $stockId);
                                $inven->setData('po_sent', 1)->save();
                            }
                       }
                        
                    // Purchase order logs just for extra information
                    // insert entry in allure_purchase_order_log
                    $model = Mage::getModel('inventory/orderlogs');
                    $logData = array(
                        'po_id' => $po_id,
                        'vendor_id' => $vendorId,
                        'date' => date("Y-m-d H:i:s"),
                        'total_amount' => $totalAmount,
                        'stock_id' => $stockId
                    );
                    $model->setData($logData);
                    $model->save()->getId();
                    try {
                        // Send notification to Admin
                        $templateId = Mage::getStoreConfig('allure_vendor/general/purchase_order_create', $storeId);
                        $adminEmail = Mage::getStoreConfig('allure_vendor/general/admin_email', $storeId);
                        // sendEmail($po_id, $vendorEmail,$templateId,$templateId)
                        if (! empty($adminEmail)) {
                            $adminEmail = explode(',', $adminEmail);
                        }
                        $helper->sendEmail($po_id, '', $templateId, $adminEmail, true);
                    } catch (Exception $e) {}
	                           
	                            
	                }  //Vendor loop
	                
	            } //foreach items array loop
	            
	    }
	   
	}
	function validateDate($date, $format = 'Y-m-d H:i:s')
	{
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) == $date;
	    
	}
	public function updateBackorderDate(){
	    $storeId=1;
	    $productArr=array();
	    
	    $collection = Mage::getModel('catalog/product')->getCollection()
	    ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
	    $ids=$collection->getAllIds();
	    $todaysDate=date('d-m-Y');
	    foreach ($ids as $id){
	        $productBackTime=Mage::getModel('catalog/product')->setStoreId($storeId)->load($id)->getBackorderTime();
	        if(!is_null($productBackTime) && !empty($productBackTime)){
	            if($this->validateDate($productBackTime, 'F j, Y')){
	                $productDate=date('d-m-Y', strtotime( $productBackTime));
	                if(strtotime($productDate) < strtotime($todaysDate)){
	                    $productArr[]=$id;
	                }
	            }
	        }
	        unset($productBackTime);
	        
	    }
	    
	    try {
	        //changed after MT-1047 request from "in 6 to 8 weeks" to "in 8 to 12 weeks";
            $backDate='in 8 to 12 weeks';

            if(count($productArr) > 0){
	            Mage::getResourceSingleton('catalog/product_action')
	            ->updateAttributes($productArr, array(
	                'backorder_time' => $backDate
	            ), $storeId);
	            Mage::log("Product Backorder date::".json_encode($productArr),Zend_log::DEBUG,'backorder_date.log',true);
	        }
	        
	    } catch (Exception $e) {
	        
	    }
	    
	}
}