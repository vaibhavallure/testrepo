<?php
require_once('../app/Mage.php');
umask(0);
Mage::app('admin');
$collection = Mage::getResourceModel('catalog/product_collection');
$absentCount=0;
$presentCount=0;
try {
	foreach ($collection as $product){
    $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
    $items = $mediaApi->items($product->getId());
    foreach($items as $item){
        $image_url = Mage::getBaseDir('media').'/catalog/product'.$item['file'];
        if(!file_exists($image_url)){
        	$absentCount++;
        	Mage::log("Product Id:".$product->getId()."--------Absent",Zend_log::DEBUG,'noimage.log',true);
        	Mage::log("absentCount:".$absentCount,Zend_log::DEBUG,'noimage.log',true);
            $mediaApi->remove($product->getId(), $item['file']);
        }else {
        	$presentCount++;
        	Mage::log("Product Id:".$product->getId()."--------Present",Zend_log::DEBUG,'noimage.log',true);
        	Mage::log("presentCount:".$presentCount,Zend_log::DEBUG,'noimage.log',true);
        }
    }
}
}catch (Exception $e) {
	Mage::log("exception:".$e,Zend_log::DEBUG,'noimage.log',true);
}
echo "Absent Count:".$absentCount."<br>";
Mage::log("absentCount:".$absentCount,Zend_log::DEBUG,'noimage.log',true);
echo "Present Count:".$presentCount."<br>";
Mage::log("Present Count:".$presentCount,Zend_log::DEBUG,'noimage.log',true);
echo "DOne";
die;