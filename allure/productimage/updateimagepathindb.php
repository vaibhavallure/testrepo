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


if(!file_exists($oldFolder)) {
    if( !file_exists($newFolder))
        die("new images folder not found");
}

if(file_exists($newFolder) && file_exists($originalFolder)){

        if(rename($originalFolder, $oldFolder)) {
            Mage::log("original folder renamed as old file", Zend_Log::DEBUG, 'updateimg.log', true);
        if(rename($newFolder, $originalFolder))
                Mage::log("new folder renamed as original folder", Zend_Log::DEBUG, 'updateimg.log', true);
            else
                Mage::log("issue with new folder rename", Zend_Log::DEBUG, 'updateimg.log', true);

        }
        else
        {
            Mage::log("issue with original folder rename ",Zend_Log::DEBUG,'updateimg.log',true);

        }

    }




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


//var_dump($image_attr_id,$smallimage_attr_id,$thumbnail_attr_id);


$csvFile=Mage::getBaseDir('var') . DS . 'export'."/rename_images_".$sku.".csv";

if(!file_exists($csvFile))
    die("csv file not found");


$csv = new Varien_File_Csv();
$data = $csv->getData($csvFile);


if (defined('STDIN')) {
    if(trim($argv[2])=="all")
    {
        $lowerlimit = 1;
        $upperlimit = count($data);
    }
    else if($argv[2] && $argv[3])
    {
        $lowerlimit = $argv[2];
        $upperlimit = $argv[3];
    }
} else {
    if (isset($_GET['all'])) {
        $lowerlimit = 1;
        $upperlimit = count($data);
    } else {
        $lowerlimit = (isset($_GET['lower'])) ? $_GET['lower'] : 0;
        $upperlimit = (isset($_GET['upper'])) ? $_GET['upper'] : 0;
    }
}

try {
    $resource = Mage::getSingleton('core/resource');
    $writeAdapter = $resource->getConnection('core_write');
    $readConnection = $resource->getConnection('core_read');

$writeAdapter->beginTransaction();
$recordIndex = 0;

for ($i=$lowerlimit;$i<=$upperlimit;$i++)
{

    $recordIndex += 1;
    $valueid=$data[$i][0];
    $entityid=$data[$i][1];
    $path=$data[$i][7];
    $position=$data[$i][5];

    //echo $valueid." ".$entityid." ".$path.'<br>';


    $writeAdapter->update(
        "catalog_product_entity_media_gallery",
        array("value" => $path),
        "value_id=".$valueid
    );



    if($position==1) {
        $writeAdapter->update(
            "catalog_product_entity_varchar",
            array("value" => $path),
            "(`attribute_id`=".$image_attr_id." AND entity_id=".$entityid.") OR  (`attribute_id`=".$smallimage_attr_id." AND entity_id=".$entityid.") OR  (`attribute_id`=".$thumbnail_attr_id." AND entity_id=".$entityid.")"
        );
    }


            if (($recordIndex % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
                Mage::log('COMMIT COUNT :: '.$recordIndex, Zend_Log::DEBUG, 'updateimg.log', true);
            }

    Mage::log('COUNT :: '.$recordIndex, Zend_Log::DEBUG, 'updateimg.log', true);
    $writeAdapter->commit();

    }

    Mage::log('DONE COUNT :: '.$recordIndex, Zend_Log::DEBUG, 'updateimg.log', true);


}catch (Exception $e)
{
    Mage::log("Exception -:".$e->getMessage(),Zend_Log::DEBUG,'updateimg.log',true);
    $writeAdapter->rollback();

}


die("Done");

