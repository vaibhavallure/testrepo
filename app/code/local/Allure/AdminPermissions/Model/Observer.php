<?php

class Allure_AdminPermissions_Model_Observer
{
    const SALES_ORDER       = "order";
    const SALES_INVOICE     = "invoice";
    const SALES_SHIPMENT    = "shipment";
    const SALES_CREDITMEMO  = "creditmemo";
    const TEAMWORK          = 2;

    /**
     * Before we save the role, let's include our restrict value
     *
     * @param $observer
     */
    public function saveRolesPermissions($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $role = $observer->getEvent()->getObject();
        $restrictByStore = (bool) $request->getParam('restrict_by_store');
        $role->setRestrictByStore($restrictByStore);
    }

    /**
     * Format and save the store restriction
     *
     * @param $observer
     */
    public function saveUserStoreRestriction($observer)
    {
        $user = $observer->getEvent()->getObject();
        if($user!=null){
	        if ($user->getStoreRestrictions()!=null) {
	            $storeRestrictions = implode(',', $user->getStoreRestrictions());
	            $user->setStoreRestrictions($storeRestrictions);
	        }
        }
    }

    /**
     * Add filter to restrict orders by store
     *
     * @param $observer
     */
    public function filterOrdersByAdminStoreRestrictions($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $orderGridCollection = $observer->getEvent()->getOrderGridCollection();
        if($user != null){
	        /* if ($user->getStoreRestrictions()!=null) {
	            $this->_filterByStoreRestriction($user, $orderGridCollection);
	        } */
	        return $this->showTeamworkOrder($user, $orderGridCollection, self::SALES_ORDER);
        }
    }

    /**
     * Add filter to restrict invoices by store
     *
     * @param $observer
     */
    public function filterInvoicesByAdminStoreRestrictions($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $invoiceGridCollection = $observer->getEvent()->getOrderInvoiceGridCollection();
        if($user != null){
	        /* if ($user->getStoreRestrictions()!=null) {
	            $this->_filterByStoreRestriction($user, $invoiceGridCollection);
	        } */
            return $this->showTeamworkOrder($user, $invoiceGridCollection, self::SALES_INVOICE);
        }
    }

    /**
     * Add filter to restrict shipments by store
     *
     * @param $observer
     */
    public function filterShipmentsByAdminStoreRestrictions($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $shipmentGridCollection = $observer->getEvent()->getOrderShipmentGridCollection();
        if($user != null){
	        /* if ($user->getStoreRestrictions()) {
	            $this->_filterByStoreRestriction($user, $shipmentGridCollection);
	        } */
            return $this->showTeamworkOrder($user, $shipmentGridCollection, self::SALES_SHIPMENT);
        }
    }

    /**
     * Add filter to restrict credit memos by store
     *
     * @param $observer
     */
    public function filterCreditmemosByAdminStoreRestrictions($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $creditmemoGridCollection = $observer->getEvent()->getOrderCreditmemoGridCollection();
        if($user!=null){
	        /* if ($user->getStoreRestrictions()!=null) {
	            $this->_filterByStoreRestriction($user, $creditmemoGridCollection);
	        } */
            return $this->showTeamworkOrder($user, $creditmemoGridCollection, self::SALES_CREDITMEMO);
        }
    }

    /**
     * Add filter to restrict transactions / payments by store
     *
     * @param $observer
     */
    public function filterPaymentsByAdminStoreRestrictions($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $creditmemoGridCollection = $observer->getEvent()->getOrderCreditmemoGridCollection();
        if($user!=null){
	        if ($user->getStoreRestrictions()!=null) {
	            $this->_filterByStoreRestriction($user, $creditmemoGridCollection);
	        }
        }
    }
    
    public function filterProductsByAdminStoreRestrictions($observer){
    	$user = Mage::getSingleton('admin/session')->getUser();
    	$collection = $observer->getEvent()->getCollection();
    	//Mage::log(get_class($collection),Zend_log::DEBUG,'abc',true);
    	if (!$collection) {
    		return $this;
    	}
    	if($user!=null){
    		if($user->getRole()->getRestrictByStore()){
		    	if ($user->getStoreRestrictions()!=null) {
		    		$storeRestrictions = explode(',', $user->getStoreRestrictions());
		    		if(in_array(0, $storeRestrictions)){
		    			return $this;
		    		}
		    		$collection->addStoreFilter($storeRestrictions[0]);
		    	}
    		}
    	}
    	return $this;
    }
    
    public function filterStoresByAdminStoreRestrictions($observer){
    	//Mage::log("hiiii",Zend_log::DEBUG,'abc',true);
    }
    
    private function isAllowedController($controllerName){
    	$controllerArray = array("catalog_product","system_config");
    	return in_array($controllerName, $controllerArray);
    }
    
    public function setStore($contollerName){
    	$user = Mage::getSingleton('admin/session')->getUser();
    	if($user!=null){
	    	if ($user->getRole()->getRestrictByStore()) {
	    		if($user->getStoreRestrictions()!=null){
		    		$storeRestrictions = explode(',', $user->getStoreRestrictions());
		    		if(!in_array(0, $storeRestrictions)){
			    		$request = Mage::app()->getRequest();
			    		$params = $request->getParams();
			    		$storeParam = $params['store'];
			    		//Mage::log($storeParam,Zend_log::DEBUG,'abc',true);
			    		if(isset($storeParam) && !empty($storeParam)){
			    			if(in_array($storeParam, $storeRestrictions)){}
			    			else{
			    				$params['store'] = $storeRestrictions[0];
			    				$request->setParams($params);
			    			}
			    		}else{
			    			$params['store'] = $storeRestrictions[0];
			    		}
			    		if($contollerName == "system_config"){
			    			$store = Mage::getModel('core/store')->load($params['store']);
			    			$storeCode = $store->getCode();
			    			$website = Mage::getModel('core/website')->load($store->getWebsite());
			    			$websiteCode = $website->getCode();
			    			$params['store'] = $storeCode;
			    			$params['website'] = $websiteCode;
			    		}
			    		$request->setParams($params);
			    	}
		    	}
    		}
    	}
    }
    
    public function hookToControllerActionPreDispatch($observer){
    	if(Mage::app()->getStore()->isAdmin()){
	    	$contollerName = Mage::app()->getRequest()->getControllerName();
	    	$actionName = Mage::app()->getRequest()->getActionName();
	    	
	    	if($this->isAllowedController($contollerName)){
	    		$this->setStore($contollerName);
	    	}
    	}
    	
    	
    }
    

    /**
     * Apply the store filters for admin user
     *
     * @param $user
     * @param $collection
     */
    protected function _filterByStoreRestriction($user, $collection)
    {
    	if($user->getRole()->getRestrictByStore()){
	        $storeRestrictions = explode(',', $user->getStoreRestrictions());
	        if(in_array(0, $storeRestrictions)){
	        	return $collection;
	        }
	        $collection ->addFieldToFilter('main_table.store_id',array('in'=>$storeRestrictions));
    	}
        return $collection;
    }
      
    
    /**
     * escape teamwork order for other user instead Administrators
     */
    public function showTeamworkOrder($user, $collection, $type = null){
        $helper = Mage::helper("allure_adminpermissions");
        if(!$helper->isShowTeamworkOrders()){
            if($type == self::SALES_ORDER){
                $controllerName = Mage::app()->getRequest()->getControllerName();
                if($controllerName == "customer"){
                    $collection->getSelect()->join(
                        array('sales_flat_order' => $collection->getTable('sales/order')),
                        'main_table.entity_id = sales_flat_order.entity_id',
                        array('create_order_method' => 'sales_flat_order.create_order_method')
                        );
                    $collection ->addFieldToFilter('sales_flat_order.create_order_method', array('nin' => array(self::TEAMWORK)));
                }else{
                    $collection ->addFieldToFilter('sales_flat_order.create_order_method', array('nin' => array(self::TEAMWORK)));
                }
            }elseif (($type == self::SALES_INVOICE) || ($type == self::SALES_SHIPMENT) || ($type == self::SALES_CREDITMEMO)){
                $collection->getSelect()->join(
                    array('sales_flat_order' => $collection->getTable('sales/order')),
                    'main_table.order_id = sales_flat_order.entity_id',
                    array('create_order_method' => 'sales_flat_order.create_order_method')
                    );
                $collection ->addFieldToFilter('sales_flat_order.create_order_method', array('nin' => array(self::TEAMWORK)));
            }
        }
        return $collection;
    }

}
