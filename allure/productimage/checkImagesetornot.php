<?php
/**
 * Created by PhpStorm.
 * User: adityagatare
 * Date: 12/11/18
 * Time: 7:19 PM
 */



require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


if (defined('STDIN')) {
    $sku = $argv[1];
} else {

    if(isset($_GET['sku']) && !empty($_GET['sku']))
        $sku=$_GET['sku'];
    else
        die("plz mention first letter of sku");

}

if($sku==null)
    die("plz mention first letter of sku");


$newFolder  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product' .DS. $sku.'_NEW';
$oldFolder  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product' .DS. $sku.'_OLD';
$originalFolder  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product' .DS. $sku;

$folder=Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product';


$attribute_code = "image";
$attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
$attribute = $attribute_details->getData();
$image_attr_id=$attribute['attribute_id'];

$attribute_code = "small_image";
$attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
$attribute = $attribute_details->getData();
$smallimage_attr_id=$attribute['attribute_id'];

$attribute_code = "thumbnail";
$attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
$attribute = $attribute_details->getData();
$thumbnail_attr_id=$attribute['attribute_id'];



$resource = Mage::getSingleton('core/resource');

$readConnection = $resource->getConnection('core_read');

try {
    $query = "SELECT cv.value FROM `catalog_product_entity_media_gallery` gal JOIN `catalog_product_entity` prod ON prod.entity_id=gal.entity_id JOIN catalog_product_entity_varchar cv ON prod.entity_id=cv.entity_id WHERE prod.sku LIKE '{$sku}%' AND cv.attribute_id={$image_attr_id} GROUP BY cv.entity_id";

    $results = $readConnection->fetchAll($query);
$i=0;
    foreach ($results as $res)
    {
        if(!file_exists($folder.$res['value'])) {
            echo $folder . $res['value'] . "<br>";
            $i++;
        }
    }

    echo $i;
}
catch (Exception $e)
{
    Mage::log("Exception-:".$e->getMessage(),Zend_Log::DEBUG,'rename_images.log',true);
}

die("Done");

