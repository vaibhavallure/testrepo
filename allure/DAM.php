<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$name 	= $_GET['file'];
if (empty($name)){
   die("Please provide file path");
}
        
$skuIndex = 0;
$styleIndex = 1;
$attributeIndex = 2;
$sortIndex = 3;
$baseIndex = 4;
$thumbnailIndex = 5;
$smallIndex = 6;

$csv = Mage::getBaseDir('var').DS."DAM".DS.$name;
        
        
$io = new Varien_Io_File();
$productsBySku = array();
        //a product model instance
$productModel = Mage::getSingleton('catalog/product');
        //read the csv
$io->streamOpen($csv, 'r');



$iow = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'DAM' .DS. 'New' ;
$newFilename = "New_".$name;
$file = $path . DS . $newFilename ;
$iow->setAllowCreateFolders(true);
$iow->open(array('path' => $path));
$iow->streamOpen($file, 'w+');
$iow->streamLock(true); 

$header = array('sku'=>'sku','style'=>'style','attribute'=>'attribute',
    'sort'=>'sort','base'=>'base','thumbnail'=>'thumbnail','small'=>'small'
);
$iow->streamWriteCsv($header);

        
$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
try{
       $writeAdapter->beginTransaction();
       $csvData = $io->streamReadCsv();
       while($csvData = $io->streamReadCsv()){
       if (count($csvData) < 2) {
                    continue;
       }
        $sku = trim($csvData[$skuIndex]);
        $style = trim($csvData[$styleIndex]);
        $attribute = trim($csvData[$attributeIndex]);
        $sort = trim($csvData[$sortIndex]);
        $base = trim($csvData[$baseIndex]);
        $thumbnail = trim($csvData[$thumbnailIndex]);
        $small = trim($csvData[$smallIndex]);
        
        $lastIndex = strripos($sku,"_");
        $firstPart = substr($sku,0, $lastIndex+1);
        $seconfPart = substr($sku,$lastIndex+1);
        $seconfPartD = $seconfPart;//preg_replace('/\D/', '', $seconfPart);
        //$sku = substr($sku,1, strripos($sku,"_"));
       // $sku= str_replace("_","|","Hello world!");
        //$productsBySku[$sku]=array($sku,$style,$attribute,$sort,$base,$thumbnail,$small);
        if(array_key_exists($firstPart,$productsBySku)){
            $tempData = $productsBySku[$firstPart]['data'];
            $tempData[] =  $seconfPartD;
            $productsBySku[$firstPart]['data'] = $tempData;
        }else{
            $productsBySku[$firstPart]=array('data'=>array($seconfPartD),
                'sku'=>$firstPart,/* 'style'=>$style,'attribute'=>$attribute,
                'sort'=>$sort,'base'=>$base,
                'thumbnail'=>$thumbnail,'small'=>$small */
            );
        }
        
        $productsBySku[$firstPart]['style']= $style;
        $productsBySku[$firstPart]['attribute']=$attribute;
        $productsBySku[$firstPart]['sort']=$sort;
        $productsBySku[$firstPart]['base']=$base;
        $productsBySku[$firstPart]['thumbnail']=$thumbnail;
        $productsBySku[$firstPart]['small']=$small;
        
        //echo "sku - ".$sku." style - ".$style."</br>";
    }
    $writeAdapter->commit();
}catch (Exception $e) {
            $writeAdapter->rollback();
}

asort($productsBySku);

/* foreach ($productsBySku as $value){
    echo "<pre>";
    print_r($value) ;
    echo "<br>";
}

die; */

/* echo "<pre>";
print_r($productsBySku);
die; */

$mainA = array();
foreach ($productsBySku as $value){
    $newSkuArr = array();
    $newSku = $value['sku'];
    $dataA = $value['data'];
    if(count($dataA) == 1){
        $newSku1 = $newSku.$dataA[0];
        $newSkuArr['sku'] = $newSku1;
    }else{
        $tNumA = array();
        foreach ($dataA as $val){
            $num = preg_replace('/\D/', '', $val);
            if($num){
                $tNumA[$val] = $num;
            }else{
                $newSku2 = $newSku . $val;
                $newSkuArr['sku'] = $newSku2;
            }
        }
        
        if(count($tNumA) > 0){
            $max = max($tNumA);
            $keyI = array_search($max, $tNumA);
            $newSku3 = $newSku  .$keyI;//.$max. '.png';
            $newSkuArr['sku'] = $newSku3;
        }
    }
    
    $newSkuArr['style'] =$value['style'];
    $newSkuArr['attribute']=$value['attribute'];
    $newSkuArr['sort']=$value['sort'];
    $newSkuArr['base']=$value['base'];
    $newSkuArr['thumbnail']=$value['thumbnail'];
    $newSkuArr['small']=$value['small'];
    //$iow->streamWriteCsv($newSkuArr);
    $mainA[] = $newSkuArr;
}


foreach($mainA as $key => $value)
{
    $aSku = $value['sku'];
    $olsku = $aSku;
    $aSku = explode(".png", $aSku);
    foreach ($mainA as $key2=>$value1){
        if( preg_match('/'.$aSku[0].'/',$value1['sku']) && ($aSku[1]!="_.png" || $aSku[1]!= "-.png")){
            if(strlen($olsku) != strlen($value1['sku'])){
               // echo "sku - ".$value1['sku']. " present"."</br>";
                unset($mainA[$key]);
            }
        }
    }
} 
foreach($mainA as $key => $value)
{
    $iow->streamWriteCsv($value);
    
}





/* foreach($productsBySku as $sku=>$value)
{
    echo "<pre>";
    print_r($value) ;
    echo "<br>";
} */

die("Operation end...");
?>


        
        
