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

$sku=null;

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



$folderPath   = Mage::getBaseDir('var') . DS . 'export';
$filename     = "rename_images_".$sku.".csv";
$filepath     = $folderPath . DS . $filename;

$io = new Varien_Io_File();
$io->setAllowCreateFolders(true);
$io->open(array("path" => $folderPath));
$csv = new Varien_File_Csv();


$resource = Mage::getSingleton('core/resource');

$readConnection = $resource->getConnection('core_read');

try {
    $query = "SELECT gal.value_id,gal.entity_id product_id,prod.sku sku ,gal.value path,val.label label,val.position position FROM `catalog_product_entity_media_gallery` gal JOIN `catalog_product_entity_media_gallery_value` val ON gal.value_id=val.value_id JOIN `catalog_product_entity` prod ON prod.entity_id=gal.entity_id WHERE prod.sku LIKE '{$sku}%' GROUP BY val.value_id";

    $results = $readConnection->fetchAll($query);
    $data = array();



    foreach ($results as $res) {

        $ext=explode(".",$res['path'])[1];
        $pathArray=array();$pathArray_copy=array();
        $pathArray_copy[0]=substr($res['sku'],0,1)."_NEW";
        $pathArray[0]=substr($res['sku'],0,1);
        $pathArray_copy[1]=$pathArray[1]=substr($res['sku'],1,1);


        if (strpos($res['label'], "model") !== false || strpos($res['path'], "model") !== false)
            /*$res['img_name']*/ $pathArray_copy[2]=$pathArray[2] = str_replace(" ", "_", str_replace("|", "-", $res['sku'])) . "_model.".$ext;
        else
            /*$res['img_name']*/ $pathArray_copy[2]=$pathArray[2] = str_replace(" ", "_", str_replace("|", "-", $res['sku'])) . "_" . $res['position'].".".$ext;


        $res['copy_image_path']="/".implode("/",$pathArray_copy);
        $res['new_image_path']="/".implode("/",$pathArray);

        $data[] = $res;

    }


    $csv->saveData($filepath, $data);

}
catch (Exception $e)
{
    Mage::log("Exception-:".$e->getMessage(),Zend_Log::DEBUG,'rename_images.log',true);
}
die("Done");

