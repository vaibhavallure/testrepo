<?php

require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);



$fileName="./positions.csv";
$lines = file($fileName);


$resource = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');
$writeAdapter->beginTransaction();

foreach ($lines as $lineNumber => $line) {

    WLog("---------------------------------------------------------------------");
    WLog("Updating: ".$line);

    $data = explode(',', $line);
    $category_id=$data[0];
    $productSKU=$data[1];
    $productPosition=$data[2];
    $product_id = Mage::getModel('catalog/product')->getIdBySku(trim($productSKU));

    if($product_id) {

        $query="UPDATE `catalog_category_product` SET `position` = '".$productPosition."' WHERE `catalog_category_product`.`category_id` = ".$category_id." AND `catalog_category_product`.`product_id` = ".$product_id.";";
        try{
            $writeAdapter->query($query);
            $writeAdapter->commit();
            WLog("Updated");
        }catch (Exception $e)
        {
            WLog("Exception: ".$e->getMessage());
            $writeAdapter->rollback();
        }
    }else{
        WLog("Product Not Found");
    }

    WLog("---------------------------------------------------------------------");

}






function WLog($message)
{
    print $message."\n";
    Mage::log($message,7,"category_product_position_update.log",true);
}