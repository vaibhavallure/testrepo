<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);

$upper = $_GET["upper"];
$lower = $_GET["lower"];

if(empty($upper) && empty($lower)){
    die("Please mention upper and lower limit.");
}

$metal_colors = getMetalColors();

$productHeader = array();
$productHeader["entity_id"] = "entity_id";
$productHeader["sku"]       = "sku";
$productHeader["name"]      = "name";
$productHeader["price"]     = "price";

$productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
foreach ($productAttrs as $productAttr) {
    $productHeader[$productAttr->getAttributeCode()] = $productAttr->getAttributeCode();
};

$collection = Mage::getResourceModel('catalog/product_collection');
$collection->addAttributeToSelect('*');
$collection->addAttributeToFilter('entity_id',
    array(
        'gteq' => $lower
    ));

$collection->addAttributeToFilter('entity_id', array(
    'lteq' => $upper
));

$io = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'export' ;
$name = "product_data_".$lower."_to_".$upper;
$file = $path . DS . $name . '.csv';
$io->setAllowCreateFolders(true);
$io->open(array('path' => $path));
$io->streamOpen($file, 'w+');
$io->streamLock(true);


$io->streamWriteCsv($productHeader);

$cnt = 0;
foreach($collection as $product) {
    $productData = array();
    foreach($productHeader as $header){
        $productData[$header] = $product->getData($header);
        if($header == "metal_color"){
            $productData[$header] = $metal_colors[$productData[$header]];
        }
    }
    $io->streamWriteCsv($productData);
    $productData = null;
}


function getMetalColors(){
    $atributeCode = 'metal_color';
    $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
    $options = $attribute->getSource()->getAllOptions();
    $metalColors = array();
    foreach($options as $option){
        $metalColors[$option['value']] = $option['label'] ;
    }
    return $metalColors;
}


die('Finish...');
