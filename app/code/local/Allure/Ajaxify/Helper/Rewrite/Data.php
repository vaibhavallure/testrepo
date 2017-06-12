<?php

class Allure_Ajaxify_Helper_Rewrite_Data extends ECP_Ajaxify_Helper_Data {
	public function __construct()
	{
		$storeCode = Mage::app()->getStore()->getCode();
		//$_SESSION['allure'] = $storeCode;
		parent::__construct();
		//Mage::log(debug_backtrace()[5]['function'],Zend_Log::DEBUG,'abc',true);
		if(!Mage::app()->getStore()->isAdmin())
			Mage::app()->setCurrentStore($storeCode);
	}
}