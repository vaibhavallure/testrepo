<?php

class Alluremultistore_Productwebsite_Model_Observer 
{
	private function getWebsiteIds(){
		$websiteIds = array();
		foreach (Mage::app()->getWebsites() as $website) {
			$websiteIds[] = $website->getId();
		}
		return $websiteIds;
	}
	
	public function assignWebsiteToProduct(){
		$helper = Mage::helper("allure_productwebsite");
		$logFileName = "product_website_assign.log";
		if($helper->getProductAssignCronStatus()){
			$debugStatus = false;
			if($helper->getDebugStatus())
				$debugStatus = true;
			
			$productStatus = -1;
			
			if($helper->getChangeProductStatus()){
				if($helper->getProductStatus())
					$productStatus = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
				else
					$productStatus = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
			}
			
			Mage::log("Product Set Status - ".$productStatus,Zend_Log::DEBUG,$logFileName,$debugStatus);
			
			$websiteIds = array();
			$stockIds = array();
			
			try{
				foreach (Mage::app()->getWebsites() as $website) {
					$websiteIds[] = $website->getId();
					$stockIds[] = $website->getStockId();
				}
				Mage::log("-----------------------------------------------------------------------",Zend_Log::DEBUG,$logFileName,$debugStatus);
				Mage::log("Assign Product To Website Time :".date('Y-m-d h:i:s'),Zend_Log::DEBUG,$logFileName,$debugStatus);
				
				Mage::getSingleton('core/session', array('name' => 'adminhtml'));
				Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
				
				$collection = Mage::getResourceModel('catalog/product_collection');
				
				$resource = Mage::getSingleton('core/resource');
				
				$collection->getSelect()->joinLeft(array('product_website'=> $resource->getTableName('catalog/product_website')),
						'product_website.product_id=e.entity_id',
						array(), null,'left')
						->where("product_website.website_id is null");
				
				$productIds = $collection->getAllIds();
				
			/* 	echo "<pre>";
				print_r($productIds);
				die; */
				
				Mage::log("===================== Website IDs =====================",Zend_Log::DEBUG,$logFileName,$debugStatus);
				Mage::log(implode($websiteIds, ","),Zend_Log::DEBUG,$logFileName,$debugStatus);
				Mage::log("===================== Product IDs list updated by Website =====================",Zend_Log::DEBUG,$logFileName,$debugStatus);
				
				if(count($productIds)>0){
					
					Mage::log(implode($productIds,","),Zend_Log::DEBUG,$logFileName,$debugStatus);
					Mage::getModel('catalog/product_website')->addProducts($websiteIds, $productIds);
					
					foreach ($productIds as $_product){
						$product = Mage::getModel('catalog/product')->load($_product);
						Mage::log("Before Product status - ".$product->getStatus(),Zend_Log::DEBUG,$logFileName,$debugStatus);
						if($productStatus !== -1){
							//$product->setStatus($productStatus)->save();
							Mage::getModel('catalog/product_status')->updateProductStatus($_product, 0, $productStatus);
							Mage::log("After Product status - ".$product->getStatus(),Zend_Log::DEBUG,$logFileName,$debugStatus);
						}
						
						$websiteIds = $product->getWebsiteIds();
						foreach ($websiteIds as $websiteId){
							$website = Mage::getModel('core/website')->load($websiteId);
							$storeIds = $website->getStoreIds();
							foreach ($storeIds as $storeId){
								$product1 = Mage::getModel('catalog/product')->setStoreId($storeId)->load($_product);;
								if($product1->getDescription()==$product->getDescription()){
									Mage::getSingleton('catalog/product_action')->updateAttributes(
											array($_product),
											array( 'description' => $product->getDescription()),
											$storeId
									);
									Mage::log("Product Description set: Product Id - ".$_product." Store Id - ".$storeId,
											Zend_Log::DEBUG,$logFileName,$debugStatus);
								}
								/* try {
										$product2 = Mage::getModel('catalog/product')->setStoreId($storeId)->load($_product);
										$priceRule = $website->getWebsitePriceRule();
										Mage::log('priceRule::'.$priceRule, Zend_Log::DEBUG, $logFileName, true);
										$oldPrice = $product->getPrice();
										$newPrice = $oldPrice * $priceRule;
										Mage::log('newPrice::'.$newPrice, Zend_Log::DEBUG, $logFileName, true);
										$product2->setPrice($newPrice)->save();
									
										
								} catch (Exception $e) {
									Mage::log('Exception Occured to set price::'.$e->getMessage(), Zend_Log::DEBUG, $logFileName, true);
								} */
								
								//Mage::getModel('catalog/product_status')->updateProductStatus($_product, $storeId, $productStatus);
							}
							
						}
					
						
						foreach ($stockIds as $stockId){
							$stockItem = Mage::getModel('cataloginventory/stock_item')
								->assignProductToNewStockByScript($product,$stockId);
								
							$inventory_msg = "Inventory Already exist.";
							if(is_null($stockItem->getItemId()) || $stockItem->getItemId()==0){
								$inventory_msg = "New Inventory Created.";
								
								$item = Mage::getModel('cataloginventory/stock_item')
									->assignProductToNewStockByScript($product,1);
								
								if(!is_null($item->getItemId())){
									$data = $item->getData();
									if(array_key_exists('item_id',$data)){
										unset($data['item_id']);
									}
									/* if(array_key_exists('qty',$data)){
										unset($data['qty']);
									} */
									$data[stock_id] = $stockId;
									$data['qty']=0;
									$stockItem->addData($data);
								}else{
									$stockItem->setData('stock_id', $stockId);
									$stockItem->setData('manage_stock', 1);
									$stockItem->setData('use_config_manage_stock', 0);
									$stockItem->setData('min_sale_qty', 1);
									$stockItem->setData('use_config_min_sale_qty', 0);
									$stockItem->setData('max_sale_qty', 1000);
									$stockItem->setData('use_config_max_sale_qty', 0);
								}
								
								$stockItem->save();
							}
							Mage::log("Stock Item Id - ".$stockItem->getItemId()." : Stock Id - ".$stockId." : ".$inventory_msg,Zend_Log::DEBUG,$logFileName,$debugStatus);
						}
					}
				}else{
					Mage::log("New product not found.",Zend_Log::DEBUG,$logFileName,$debugStatus);
				}
			}catch (Exception $e){
				Mage::log($e->getMessage(),Zend_Log::DEBUG,$logFileName,$debugStatus);
			}
		}else{
			Mage::log("Product Assign Website Cron Status is Disabled.",Zend_Log::DEBUG,$logFileName,true);
		}
	}
}
