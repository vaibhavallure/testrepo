<?php

require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$skuString="XET1SPSL65D,XET1SPSL8D,XET1SPLSL65D,XET1SPLSL8D,XET3SPLSL65D,XET3SPLSL8D,XIVTG25D,XIVTG3D,XIVTG4D,XIVTG5D,XCAPIVDR,XBARAPIV11DR,EBARAPIV11DR,XBARAPIV18DR,EBARAPIV18DR,XBARCRIVD11DR,EBARCRIVD11DR,XBARCRIVD18DR,XBARTS11R,EBARTS11R,XBARTS18R,XDRP2D,XCHPEF43D,XCHPEF64D,XCHPEF75D,XCHMQF63D,XCHMQF735D,XSLEG8DR,JCHBARET7D,JCHBARET11D,JCHBARET18D,JCHBARET24D,JCHBARET50D,XCHBARET7D,XCHBARET11D,XCHBARET18D,ELOTORB65D,ELOTEG3CDBAD,ELOTEG1CD";
$message="Patent Pending";
addLog("sku's:".$skuString);

$skuArr=explode(",",$skuString);
$updateSuccess=array();
$updateFail=array();

foreach ($skuArr as $sku) {
    $product_id = Mage::getModel('catalog/product')->getIdBySku(trim($sku));
    if($product_id) {
        $product = Mage::getModel('catalog/product')->load($product_id);
        $product->setPatentMessage($message);
        try{
             $product->save();
             addLog("product updated sku:".$sku);
             array_push($updateSuccess,$sku);
        }catch (Exception $e)
        {
            addLog($sku.":Exception:".$e->getMessage());
        }
    }else{
        addLog("product not found for sku:".$sku);
        array_push($updateFail,$sku);
    }
}


addLog("final result:");
addLog("sku updated successfully:");
addLog($updateSuccess);
addLog("sku update failed:");
addLog($updateFail);




function addLog($string)
{
    echo "\n".$string;
    Mage::log($string,7,"patent_message.log",true);
}