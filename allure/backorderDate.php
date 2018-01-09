<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$storeId=1;
$productArr=array();

$collection = Mage::getModel('catalog/product')->getCollection()
->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
$ids=$collection->getAllIds();
$todaysDate=date('d-m-Y');
foreach ($ids as $id){
    $productBackTime=Mage::getModel('catalog/product')->setStoreId($storeId)->load($id)->getBackorderTime();
    if(!is_null($productBackTime) && !empty($productBackTime)){
        if(validateDate($productBackTime, 'F j, Y')){
            $productDate=date('d-m-Y', strtotime( $productBackTime));
            if(strtotime($productDate) < strtotime($todaysDate)){
                $productArr[]=$id;
            }
        }
    }
    unset($productBackTime);
    
}
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
    
}
try {
    $backDate='in 4 to 6 Weeks';
    if(count($productArr) > 0){
        Mage::getResourceSingleton('catalog/product_action')
        ->updateAttributes($productArr, array(
            'backorder_time' => $backDate
        ), $storeId);
        Mage::log("Product Backorder date::".json_encode($productArr),Zend_log::DEBUG,'backorder_date.log',true);
    }
    
} catch (Exception $e) {
    
}





