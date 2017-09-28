<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$from 	= $_GET['from'];
$to 	= $_GET['to'];
$rule 	= $_GET['rule'];
$lower  = $_GET['lower'];
$upper  = $_GET['upper'];


if (empty($from))
    die("Please provide source store id");

if (empty($rule))
    die("Please provide  rule");

$count=0;
$prodCount=0;
$productModel = Mage::getSingleton('catalog/product');
$collection = Mage::getModel('catalog/product')->getCollection()->setStoreId($from);
if (! empty($lower) && ! empty($upper)) {
    $collecton->addAttributeToFilter('entity_id', array(
        'from' => $lower,
        'to' => $upper
    ));
}

$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');

try{
    $writeAdapter->beginTransaction();
    $recordIndex = 1;
    foreach ($collection as $product) {
        $_product=$productModel->setStoreId($to)->load($product->getId());
        $oldPrice=$_product->getPrice();
        if($product->getPrice() > 0){
            $_product->setPrice($product->getPrice()*$rule);
            $_product->save();
            Mage::log("Product Id:".$_product->getId()." - Old Price:".$oldPrice,Zend_log::DEBUG,'copyprices.log',true);
            Mage::log("Product Id:".$_product->getId()." - Newe Price:".$_product->getPrice(),Zend_log::DEBUG,'copyprices.log',true);
            Mage::log("Product Count:".$recordIndex,Zend_log::DEBUG,'copyprices.log',true);
        }
        else{
            $productPriceNotFound[]=$product->getId();
        }
        if (($recordIndex % 250) == 0) {
            $writeAdapter->commit();
            $writeAdapter->beginTransaction();
        }
        $recordIndex += 1;
    }
    $writeAdapter->commit();
}catch (Exception $e) {
    $writeAdapter->rollback();
}
Mage::log("Products not found:".json_encode($productPriceNotFound),Zend_log::DEBUG,'copyprices.log',true);
die("Operation end...");
