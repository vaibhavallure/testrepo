<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::log("Backorder_time Update -------script start",Zend_log::DEBUG,'backorder_date.log',true);

Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$storeId=0;
$productArr=array();

$collection = Mage::getModel('catalog/product')->getCollection()
 ->addAttributeToFilter('backorder_time', array('neq' => null));

$productArr=$collection->getAllIds();



var_dump($collection->getSelect()->__toString());
Mage::log("Number Of Products :".count($productArr),Zend_log::DEBUG,'backorder_date.log',true);


try {
    $backDate='in 8 to 12 Weeks';
    if(count($productArr) > 0){
        Mage::getResourceSingleton('catalog/product_action')
        ->updateAttributes($productArr, array(
            'backorder_time' => $backDate
        ), $storeId);
        Mage::log("Product Backorder date::".json_encode($productArr),Zend_log::DEBUG,'backorder_date.log',true);
    }
    
} catch (Exception $e) {
    Mage::log("Exception: ".$e->getMessage(),Zend_log::DEBUG,'backorder_date.log',true);

}

Mage::log("Backorder_time Update -------script end",Zend_log::DEBUG,'backorder_date.log',true);





