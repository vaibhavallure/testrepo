<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$name 	= $_GET['file'];
if (empty($name))
   die("Please provide file path");
        
        
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
        
$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
try{
       $writeAdapter->beginTransaction();
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
        $sku= 
         
       // $sku= str_replace("_","|","Hello world!");
        $productsBySku[$sku]=array($sku,$style,$attribute,$sort,$base,$thumbnail,$small);
        
        
        
    }
    $writeAdapter->commit();
}catch (Exception $e) {
            $writeAdapter->rollback();
}

asort($productsBySku);
foreach($productsBySku as $sku=>$value)
{
    echo  $sku ;
    echo "<br>";
}
?>

die("Operation end...");
        
        