<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$basePrefix='2901369';
$counter=0;

$collection=Mage::getModel("catalog/product")->getCollection();
$collection->addAttributeToFilter('status', array('eq' => 1));
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));
$collection->addFieldToFilter('entity_id',array('gteq'=>1));
$collection->addFieldToFilter('entity_id',array('lteq'=>15000));



$some_attr_code = "metal";
$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

foreach ($collection as $_product){

    $_product=Mage::getModel("catalog/product")->load($_product->getId());
    $plu=$_product->getTeamworkPlu();
    $countValue=0;
    $countValue=count(str_split(trim($plu)));
    if($countValue==2){
        $plu='000'.$plu;
    }elseif ($countValue==3){
        $plu='00'.$plu;
    }elseif ($countValue==4){
        $plu='0'.$plu;
    }
    $countValue=count(str_split(trim($plu)));
    
    if ($countValue==5){
        $gtin="";
        $counter++;
        $checkNumber=calculateChecknumber($basePrefix,$plu);
        $gtin=$basePrefix.$plu.$checkNumber;
        $_product->setGtinNumber($gtin);
        $_product->save();
        Mage::log("Count::".$counter."  ".$_product->getSku()."::".$gtin,Zend_log::DEBUG,'gstn.log',true);
        
    }else {
        
        Mage::log("FOR::".$_product->getSku()." PLU lenth::".$plu,Zend_log::DEBUG,'gstn_error.log',true);
    }
    
    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
    $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
    foreach ($simple_collection as $simpleProd){
        $_product=Mage::getModel("catalog/product")->load($simpleProd->getId());
        $plu=$_product->getTeamworkPlu();
        $countValue=0;
        
        $countValue=count(str_split(trim($plu)));
        if($countValue==2){
            $plu='000'.$plu;
        }elseif ($countValue==3){
            $plu='00'.$plu;
        }elseif ($countValue==4){
            $plu='0'.$plu;
        }
        $countValue=count(str_split(trim($plu)));
        
        if ($countValue==5){
            $gtin="";
            $counter++;
            $checkNumber=calculateChecknumber($basePrefix,$plu);
            $gtin=$basePrefix.$plu.$checkNumber;
            $_product->setGtinNumber($gtin);
            $_product->save();
            Mage::log("Count::".$counter."  ".$_product->getSku()."::".$gtin,Zend_log::DEBUG,'gstn.log',true);
            
        }else {
            Mage::log("FOR::".$_product->getSku()." PLU lenth::".$plu,Zend_log::DEBUG,'gstn_error.log',true);
        }
    }
}


function calculateChecknumber($basePrefix,$plu){
    
    $tempGSTN=$basePrefix.$plu;
    $arr = str_split($tempGSTN);
    $length = count($arr);
    
    
    //Setp 1
    $step1=0;
    for($i=$length-1;$i>0;$i=$i-2){
        $step1=$step1+$arr[$i];
    }
    
    //Setp 2
    $step2=$step1*3;
    
    //Setp 3
    $step3=0;
    for($i=$length-2;$i>=00;$i=$i-2){
        $step3=$step3+$arr[$i];
    }
    
    //Setp 4
    $setp4=$step3+$step2;
    
    //Setp 5
    $digit=0;
    for ($x = 1; $x <= 100; $x++) {
        $digit=$x*10;
        
        if($digit == $setp4){
            break;
        }
        
        if($digit > $setp4){
            break;
        }
    }
    $checkNumber=$digit-$setp4;
    return $checkNumber;
}

die('finished');