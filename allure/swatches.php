<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
$lower = $_GET['lower'];
$upper= $_GET['upper'];

if(empty($lower) || empty($upper)){
    die('Please add Upper and Lower limit');
}


$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('status',array('eq' =>1));
$collection->addAttributeToFilter('entity_id',array('gteq' => $lower));
$collection->addAttributeToFilter('entity_id',array('lteq' => $upper));
$collection->addAttributeToFilter('type_id',array('eq' => 'configurable'));

$count=1;
foreach ($collection as $_product){
    
    $childProducts = Mage::getModel('catalog/product_type_configurable')
    ->getUsedProducts(null,$_product);
    
    foreach ($childProducts as $child){
        $child=Mage::getModel('catalog/product')->load($child->getId());
        $childSku=$child->getSku();
        $childSkuArray = explode('|', $childSku);
        $order='';
        if (count($childSkuArray) >=2) {
            if(strtolower($childSkuArray[1])=='white gold'){
                $order=1;
            }elseif (strtolower($childSkuArray[1])=='yellow gold'){
                $order=2;
            }elseif (strtolower($childSkuArray[1])=='rose gold'){
                $order=3;
            }elseif (strtolower($childSkuArray[1])=='black gold'){
                $order=4;
            }else{
                $order=5;
            }
            $child->setOrder($order);
            $child->save();
            $count++;
            
            var_dump($count." SKU::".$child->getSku()." ORDER::".$order);
            Mage::log($count." SKU::".$child->getSku()." ORDER::".$order,Zend_log::DEBUG,'swatches.log',true);
        }
    }
}

