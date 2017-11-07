<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$stockId= $_GET['stock'];


if(empty($stockId)){
    die('Please insert Stock Id');
}

$count=0;
$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
echo "<pre>";
$fixedItems = array("ZSTH","XWB15BKD","XWB10D","XWB10BKD","XTHMQD","XTHD4","XTHD2","XTHBF6","XTHBF2D","XTHBF25D","XTHBF","XTHBAD","XSNPA10RB","XSNPA10DRB","XSNPA10D","XSNEG5RB","XSNEG5D","XSNEG5BKD","XSNEG10RB","XSNEG10D","XSNEG10BKD","XSN65BKD","XSN5RB","XSN5D","XSN5BKD","XSN10RB","XSN10D","XSN10BKD","XG5OP","XDS5DOP","XDS5D","XDGTR65RB","XDGTR65OP","XDGTR65D","XDGTR65BKD","XDG65D","XDG65BKD","XDG5D","ESN10RB","CWB10D_T","CWB10D_R","CWB10D_E","CWB10D_C","CWB10D_B","CWB10D","CWB10BKD_T","CWB10BKD_R","CWB10BKD_E","CWB10BKD_C","CWB10BKD_B","CWB10BKD","CTHBF_T","CTHBF_R","CTHBF_E","CTHBF_C","CTHBF_B","CTHBF","CSNPA10DRB_T","CSNPA10DRB_R","CSNPA10DRB_E","CSNPA10DRB_C","CSNPA10DRB_B","CSNPA10DRB","CSNPA10D_T","CSNPA10D_R","CSNPA10D_E","CSNPA10D_C","CSNPA10D_B","CSNPA10D","CSNEG5RB_T","CSNEG5RB_R","CSNEG5RB_E","CSNEG5RB_C","CSNEG5RB_B","CSNEG5RB","CSNEG5D_T","CSNEG5D_R","CSNEG5D_E","CSNEG5D_C","CSNEG5D_B","CSNEG5D","CSNEG10D_T","CSNEG10D_R","CSNEG10D_E","CSNEG10D_C","CSNEG10D_B","CSNEG10D","CSNEG10BKD_T","CSNEG10BKD_R","CSNEG10BKD_E","CSNEG10BKD_C","CSNEG10BKD_B","CSNEG10BKD","CSN5RB_T","CSN5RB_R","CSN5RB_E","CSN5RB_C","CSN5RB_B","CSN5RB","CSN5BKD_T","CSN5BKD_R","CSN5BKD_E","CSN5BKD_C","CSN5BKD_B","CSN5BKD","CSN10RB_T","CSN10RB_R","CSN10RB_E","CSN10RB_B","CSN10RB","CSN10D_T","CSN10D_R","CSN10D_E","CSN10D_C","CSN10D_B","CSN10D","CSN10BKD_T","CSN10BKD_R","CSN10BKD_E","CSN10BKD_C","CSN10BKD_B","CSN10BKD","CG5OP","CDS5DOP_R","CDS5DOP_C","CDS5DOP_B","CDS5DOP","CDGTR65RB_T","CDGTR65RB_R","CDGTR65RB_E","CDGTR65RB_C","CDGTR65RB_B","CDGTR65RB","CDGTR65OP_E","CDGTR65OP_C","CDGTR65OP_B","CDGTR65OP","CDGTR65D_T","CDGTR65D_R","CDGTR65D_E","CDGTR65D_C","CDGTR65D_B","CDGTR65D","CDGTR65BKD_R","CDGTR65BKD_C","CDGTR65BKD_B","CDGTR65BKD","CDG65D_T","CDG65D_R","CDG65D_C","CDG65D_B","CDG65D","CDG65BKD_E","CDG65BKD_C","CDG65BKD_B","CDG65BKD","CDG5D_B","CDG5D","CSN5D_T","CSN5D_R","CSN5D_E","CSN5D_C","CSN5D_B","CSN5D","XWB15D");


//$fixedItems=array('CCLV7DPS_C','CCLV7DPS_R','XCLVD');
$recordIndex = 0;
$name=Mage::getModel('core/website')->load($stockId)->getCode();

$writeAdapter->beginTransaction();

foreach ($fixedItems as $fixedSku) {
    $count++;
    Mage::log('Count::'.$count.'-Parent SKU :: '.$fixedSku, Zend_Log::DEBUG, 'copystock_'.$name.'_3D.log', true);
    Mage::log('Stock ID  :: '.$stockId, Zend_Log::DEBUG, 'copystock_'.$name.'_3D.log', true);
    $productId = Mage::getModel('catalog/product')->getIdBySku($fixedSku);
    $_product = Mage::getModel('catalog/product')->load($productId);
    
    $productCollection = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter('type_id', 'simple')
    ->addAttributeToFilter('sku', array ('like'=> $fixedSku.'|%'))
    ->load();
    
    foreach ($productCollection  as $product) {
        
        $oldItem = $product->getSku();
        $oldItemSku = explode('|', $oldItem);
        
        if (count($oldItemSku) ==  4) {
            
            $parentItem = $oldItemSku[0];
            
            $post_length = $oldItemSku[2];
            
            $newItem = implode('|', array($parentItem, $oldItemSku[1], $oldItemSku[3]));
            
            
          //  Mage::log('New SKU :: '.$newItem, Zend_Log::DEBUG, 'copystock2D.log', true);
          //  Mage::log('Old Item SKU :: '.$oldItemSku, Zend_Log::DEBUG, 'copystock2D.log', true);
          //  var_dump("New SKU: ".$newItem);
           // var_dump("Old Item SKU: ".$oldItem);
            //var_dump("Post Length: ".$post_length);
            
            $oldItemId = Mage::getModel('catalog/product')->getIdBySku($oldItem);
            $newItemId = Mage::getModel('catalog/product')->getIdBySku($newItem);
            try {
                if(isset($newItemId)){
                    $oldItemInventory=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($oldItemId,$stockId);
                    $newItemInventory=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($newItemId,$stockId);
                    if($newItemInventory->getId() && $oldItemInventory->getId()){
                        $newItemQty=$newItemInventory->getQty();
                        $qty=$newItemInventory->getQty()+$oldItemInventory->getQty();
                        $newItemInventory->setQty($qty)->save();
                        Mage::log('Old product(Source):: '.$oldItemId."--SKU::".$oldItem."---Qty=".$oldItemInventory->getQty(),  Zend_Log::DEBUG, 'copystock_'.$name.'_3D.log', true);
                        Mage::log('New Product(destination):: '.$newItemId."--SKU::".$newItem."---Qty=".$newItemQty."---Total Qty::".$newItemInventory->getQty(), Zend_Log::DEBUG, 'copystock_'.$name.'_3D.log', true);
                        if (($recordIndex % 250) == 0) {
                            $writeAdapter->commit();
                            $writeAdapter->beginTransaction();
                        }
                        $recordIndex += 1;
                        
                    }
                    else {
                        var_dump("Old product(Source) ID: ".$oldItemId."-SKU::".$oldItem);
                        var_dump("New Product(destination) ID".$newItemId."-SKU::".$newItem);
                        Mage::log('Old product(Source):: '.$oldItemId, Zend_Log::DEBUG, 'copystock3D_error.log', true);
                        Mage::log('New Product(destination):: '.$newItemId, Zend_Log::DEBUG, 'copystock3D_error.log', true);
                        
                        Mage::log('Old product(Source) ID:: '.$oldItemId."-SKU::".$oldItem, Zend_Log::DEBUG, 'copystock_'.$name.'_3D_error.log', true);
                        Mage::log('New Product(destination) ID:: '.$newItemId."-SKU::".$newItem, Zend_Log::DEBUG, 'copystock_'.$name.'_3D_error.log', true);
                    }
                        
                }
            } catch (Exception $e) {
                var_dump($e->getMessage());
                Mage::log('Exception Occured:: '.$e->getMessage(), Zend_Log::DEBUG, 'copystock_'.$name.'_3D_error.log', true);
            }
            
          //  Mage::log('Original Id :: '.$oldItemId, Zend_Log::DEBUG, 'copystock2D.log', true);
          //  Mage::log('New Id :: '.$newItemId, Zend_Log::DEBUG, 'copystock2D.log', true);
        
        }
    
    }
    
     Mage::log('====================================== ', Zend_Log::DEBUG, 'copystock_'.$name.'_3D.log', true);
   }

$writeAdapter->commit();


echo "Done";

die;