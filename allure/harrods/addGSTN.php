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
$collection->setOrder('sku', 'asc');

$some_attr_code = "metal";
$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

foreach ($collection as $_product){

    $_product=Mage::getModel("catalog/product")->load($_product->getId());
    $plu=$_product->getTeamworkPlu();
    if (count(str_split($plu))>=5){
        $gstn="";
        $counter++;
        $checkNumber=calculateChecknumber($basePrefix,$plu);
        $gstn=$basePrefix.$plu.$checkNumber;
        $_product->setGstnNumber($gstn);
        $_product->save();
        Mage::log("Count::".$counter."  ".$_product->getSku()."::".$gstn,Zend_log::DEBUG,'gstn.log',true);
        
    }else {
        echo "FOR::".$_product->getSku()." PLU lenth::".$plu;
        echo "<br>";
    }
    
    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
    $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
    foreach ($simple_collection as $simpleProd){
        $_product=Mage::getModel("catalog/product")->load($simpleProd->getId());
        $plu=$_product->getTeamworkPlu();
        if (count(str_split($plu))>=5){
            $gstn="";
            $counter++;
            $checkNumber=calculateChecknumber($basePrefix,$plu);
            $gstn=$basePrefix.$plu.$checkNumber;
            $_product->setGstnNumber($gstn);
            $_product->save();
            Mage::log("Count::".$counter."  ".$_product->getSku()."::".$gstn,Zend_log::DEBUG,'gstn.log',true);
            
        }else {
            echo "FOR::".$_product->getSku()." PLU lenth::".$plu;
            echo "<br>";
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
        if($digit > $setp4){
            break;
        }
    }
    $checkNumber=$digit-$setp4;
    return $checkNumber;
}

die('finished');