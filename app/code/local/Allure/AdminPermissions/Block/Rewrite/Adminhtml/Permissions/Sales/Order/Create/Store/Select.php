<?php

class Allure_AdminPermissions_Block_Rewrite_Adminhtml_Permissions_Sales_Order_Create_Store_Select 
	extends Mage_Adminhtml_Block_Sales_Order_Create_Store_Select
{
	public function getWebsiteCollection()
	{
		$collection = Mage::getModel('core/website')->getResourceCollection();
		$websiteIds = $this->getWebsiteIds();
		if (!is_null($websiteIds)) {
			$collection->addIdFilter($this->getWebsiteIds());
		}
		$user = Mage::getSingleton('admin/session')->getUser();
		$websitesArr = array();
		if($user->getStoreRestrictions()!=null){
			$storeRestrictions = explode(',', $user->getStoreRestrictions());
			foreach ($storeRestrictions as $storeId){
				$websiteId= Mage::getModel('core/store')->load($storeId)->getWebsiteId();
				$websitesArr[] = $websiteId;
			}
			$collection->addIdFilter($websitesArr);
		}
		return $collection->load();
	}
}
