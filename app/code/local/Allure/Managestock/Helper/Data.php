<?php
class Allure_Managestock_Helper_Data extends Mage_Core_Helper_Abstract
{
	const DEFAULT_STOCK_ID = 1;
	const DEFAULT_WEBSITE_ID = 1;
	const DEFAULT_STORE_ID = 1;
	const KEY_CUSTOM_STORE_ID = 'cust_store_id';
	/*
	 * 	Get stock id by using current website of
	 *  current store in magento.
	 *  This method for backend purpose.
	 *  return int stock id value.
	 */
	public function getStockId(){
		$stockId = self::DEFAULT_STOCK_ID;
		if(Mage::app()->getStore()->isAdmin()){
			$storeId = $this->getStoreIdByAdminControllerAction();
			$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
			$stockId = $this->getStockIdByWebsiteId($websiteId);
		}else{
			$stockId = Mage::app()->getWebsite()->getStockId();
		}
		return $stockId;
	}
	
	private function getStoreIdByAdminControllerAction(){
		$controllerName = Mage::app()->getRequest()->getControllerName();
		$actionName = Mage::app()->getRequest()->getActionName();
		
		$storeId = self::DEFAULT_STORE_ID;
		if($controllerName=="catalog_product"){
			$storeParam = Mage::app()->getRequest()->getParam('store');
			if(isset($storeParam) && !empty($storeParam)){
				if($storeParam!=0)
					$storeId = $storeParam;
			}
		}else{
			//if($actionName=="massCancel" || $actionName=="cancel"){
			if($controllerName == "system_convert_gui"){
				$profileIdArr = Mage::app()->getRequest()->getParams();('batch_id');
				if(array_key_exists('batch_id',$profileIdArr)){
					$profileId = $profileIdArr['batch_id'];
					$profile = Mage::getModel('dataflow/batch')->load($profileId);
				}
				
				if(array_key_exists('id',$profileIdArr)){
					$profileId = $profileIdArr['id'];
					$profile = Mage::getModel('dataflow/profile')->load($profileId);
				}
				
				if($profile->getProfileId()!=0){
					$storeId = $profile->getStoreId();
				}
				
			}
			elseif ((strpos($controllerName,"inp_")!==false) || (strpos($controllerName,"inph_")!==false)
					|| (strpos($controllerName,"inw_")!==false) || (strpos($controllerName,"inl_")!==false)){
				$storeId = 2;
			} 
			else{
				if(isset($_SESSION[self::KEY_CUSTOM_STORE_ID]) && !empty($_SESSION[self::KEY_CUSTOM_STORE_ID])){
					$storeId = $_SESSION[self::KEY_CUSTOM_STORE_ID];
				}
				
				if($controllerName == "sales_order_create"){
					$store = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
					if(isset($store)&& !empty($store)){
						$storeId = $store;
					}
				}elseif ($controllerName=="sales_orderr" || $controllerName=="sales_order"){
				    $params = Mage::app()->getRequest()->getParams();
				    if(array_key_exists("order_id", $params)){
				        $order = Mage::getModel("sales/order")->load($params['order_id']);
				        $storeId = $order->getStoreId();
				    }elseif (array_key_exists("store_id", $params)){
				        $storeId = $params['store_id'];
				    }elseif (array_key_exists("store", $params)){
				        $storeId = $params['store'];
				    }else{
				        $storeId = Mage::app()->getStore()->getId();
				    }
				}
			}
		}
		return $storeId;
	}
	
	/*
	 * Set value to the cust_store_id session variable
	 * 
	 */
	public function setStoreId($storeId){
		$storeId = (isset($storeId)
				&& !empty($storeId) ? $storeId: self::DEFAULT_STORE_ID) ;
		if($storeId==0)
			$storeId = self::DEFAULT_STORE_ID;
		$_SESSION[self::KEY_CUSTOM_STORE_ID] = $storeId;
	}
	
	/*
	* 	Retrive the current website id
	* 	By using current store i.e.
	*   store into cust_store_id session variable.
	* 	return int website id value.
	*/
	public function getWebsiteId(){
		$storeId = (isset($_SESSION[self::KEY_CUSTOM_STORE_ID])
				&& !empty($_SESSION[self::KEY_CUSTOM_STORE_ID]) ? $_SESSION[self::KEY_CUSTOM_STORE_ID]: self::DEFAULT_STORE_ID) ;
		$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
		if(isset($websiteId) && !empty($websiteId))
			return $websiteId;
		return self::DEFAULT_WEBSITE_ID;
	}
	
	/*
	 * 	Retrive stock id by website id
	 *  @param websiteId
	 *  return int stock id value.
	 */
	public function getStockIdByWebsiteId($websiteId){
		return Mage::getModel('core/website')->load($websiteId)->getStockId();
	}
	
	/*
	 * 	Get stock id by using current website of
	 *  current store in magento.
	 *  This method for frontend purpose.
	 *  return stcok id value.
	 */
	public function getStockIdOfCurrentWebsite(){
		return $this->getStockId();
	}
	
	public function getWebsiteIdByStoreId($storeId){
		return Mage::getModel('core/store')->load($storeId)->getWebsiteId();
	}
	
}