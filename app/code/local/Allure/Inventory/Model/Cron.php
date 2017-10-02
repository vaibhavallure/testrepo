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
			$collection->addFieldToFilter('allure_purchase_order.stock_id',$storeId);
			
			$productArray=array();
			foreach ($collection as $Poproducts){
			    $productArray[]=$Poproducts->getProductId();
			    
			}
			
			
			$subCollection=Mage::getModel('catalog/product')->getUsedCategoryProductCollection(Allure_Inventory_Block_Minmax::PARENT_ITEMS_CATEGORY_ID);
			$subCollection->addAttributeToSelect('entity_id')->setStoreId($storeId);
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
	
}