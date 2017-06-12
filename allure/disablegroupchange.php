<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');



$collection=  Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('group_id',2);


try {
	$writeAdapter->beginTransaction();
	foreach($collection as $cust){
		$customer = Mage::getModel('customer/customer')
		->load($cust->getId());
		$customer->setDisableAutoGroupChange(1);
		$customer->save();
		Mage::log("set for :".$customer->getId(),Zend_log::DEBUG,'disable_groups',true);
	}
	echo "Complete";
	$writeAdapter->commit();
} catch (Exception $e) {
	$writeAdapter->rollback();
}

die("Operation finished...");