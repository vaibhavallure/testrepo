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




$folderPath   = Mage::getBaseDir('var') . DS . 'export';
$date = date('Y-m-d');
$filename     = "rename_images.csv";
$filepath     = $folderPath . DS . $filename;

$io = new Varien_Io_File();
$io->setAllowCreateFolders(true);
$io->open(array("path" => $folderPath));
$csv = new Varien_File_Csv();


$resource = Mage::getSingleton('core/resource');

$readConnection = $resource->getConnection('core_read');

try {
    $query = "SELECT gal.value_id,gal.entity_id product_id,prod.sku sku ,gal.value path,val.label label,val.position position FROM `catalog_product_entity_media_gallery` gal JOIN `catalog_product_entity_media_gallery_value` val ON gal.value_id=val.value_id JOIN `catalog_product_entity` prod ON prod.entity_id=gal.entity_id WHERE prod.sku LIKE 'X%'";

    $results = $readConnection->fetchAll($query);
    $data = array();

    foreach ($results as $res) {

        $ext=explode(".",$res['path'])[1];
        if (strpos($res['label'], "model") !== false)
            $res['img_name'] = str_replace(" ", "_", str_replace("|", "-", $res['sku'])) . "#model.".$ext;
        else
            $res['img_name'] = str_replace(" ", "_", str_replace("|", "-", $res['sku'])) . "#" . $res['position'].".".$ext;
        $data[] = $res;
    }


    $csv->saveData($filepath, $data);

}
catch (Exception $e)
{
    Mage::log("Exception-:".$e->getMessage(),Zend_Log::DEBUG,'rename_images.log',true);

}
die("Done");

