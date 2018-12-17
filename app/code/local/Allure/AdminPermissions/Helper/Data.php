<?php

class Allure_AdminPermissions_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function canRestrictByStore($user)
    {
        $roles = $user->getRoles();
        foreach ($roles as $roleId) {
            $role = Mage::getModel('admin/role')->load($roleId);
            if ($role && $role->getRestrictByStore()) {
                return true;
            }
        }

        return false;
    }
    
    
    public function checkStoreAdmin(){
    	$user = Mage::getSingleton('admin/session')->getUser();
    	$flag = false;
    	if($user!=null){
	    	if($user->getRole()->getRestrictByStore()){
	    		if ($user->getStoreRestrictions()) {
	    			$storeRestrictions = explode(",", $user->getStoreRestrictions());
	    			if(!in_array(0, $storeRestrictions)){
	    				$flag = true;
	    			}
	    		}
	    	}
    	}
    	return $flag;
    }
    
    //show teamwork order data only super admin
    public function isShowTeamworkOrders(){
        $isShow = false;
        $user = Mage::getSingleton('admin/session')->getUser();
        if($user == null){
            return $isShow;
        }
        $userRole = $user->getRole()->getData();
        $roleId = $userRole["role_id"];
        if($roleId == 1){
            $isShow =  true;
        }
        return $isShow;
    }

}
