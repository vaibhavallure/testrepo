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
$update="no";

if (defined('STDIN')) {
    $sku = $argv[1];
    $update = trim($argv[2]);
} else {

    if(isset($_GET['sku']) && !empty($_GET['sku']))
        $sku=$_GET['sku'];
    else
        die("plz mention first letter of sku");

    $update=$_GET['update'];

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
$writeAdapter = $resource->getConnection('core_write');
$readConnection = $resource->getConnection('core_read');

try {
    $query = "SELECT prod.entity_id,cv.value FROM `catalog_product_entity_media_gallery` gal JOIN `catalog_product_entity` prod ON prod.entity_id=gal.entity_id JOIN catalog_product_entity_varchar cv ON prod.entity_id=cv.entity_id WHERE prod.sku LIKE '{$sku}%' AND cv.attribute_id={$image_attr_id} GROUP BY cv.entity_id";

    $results = $readConnection->fetchAll($query);
$i=0;

$data=array();
$notfound=array();

    foreach ($results as $res)
    {

        if(!file_exists($folder.$res['value'])) {
//            echo $folder . $res['value'] . "<br>\n";
            $i++;
            $foundimg=array();
            $query2 = "SELECT * FROM `catalog_product_entity_media_gallery` gal  WHERE entity_id = {$res['entity_id']}";

            $results2 = $readConnection->fetchAll($query2);

            if(count($results2)>0)
            {
                foreach ($results2 as $res2)
                {
                    if(file_exists($folder.$res2['value'])) {


                        $foundimg=array(
                            "entity_id"=>$res2['entity_id'],
                            "imgpath"=>$res2['value']
                        );

                          //echo "yes <br>";
                          break;
                    }
                    else
                    {
//                        echo "no <br>";
                    }

                }
            }

            //break;
           if(count($foundimg)>0)
           {
               $data[]=$foundimg;
           }
           else
           {
               $notfound[]=array(
                   "entity_id"=>$res['entity_id'],
                   "imgpath"=>$res['value']
               );
           }
        }
    }

    Mage::log("total number of images not set properly => ".$i,Zend_Log::DEBUG,'checkimagesetornot.log',true);
    Mage::log("other Images found for these products => ",Zend_Log::DEBUG,'checkimagesetornot.log',true);
    Mage::log($data,Zend_Log::DEBUG,'checkimagesetornot.log',true);
    Mage::log("----------------------------------------------------------------------------------------------------- ",Zend_Log::DEBUG,'checkimagesetornot.log',true);
    Mage::log("no image found for these products => ",Zend_Log::DEBUG,'checkimagesetornot.log',true);
    Mage::log($notfound,Zend_Log::DEBUG,'checkimagesetornot.log',true);






if($update=="yes" && count($data)>0) {
    /*----------------------------update image data------------------------------------------------*/


    try {
        $writeAdapter->beginTransaction();
        $recordIndex = 0;
        Mage::log('updating start----', Zend_Log::DEBUG, 'checkimagesetornot.log', true);

        foreach ($data as $d) {

            $recordIndex += 1;
            $path = $d['imgpath'];
            $entityid = $d['entity_id'];


            $writeAdapter->update(
                "catalog_product_entity_varchar",
                array("value" => $path),
                "(`attribute_id`=" . $image_attr_id . " AND entity_id=" . $entityid . ") OR  (`attribute_id`=" . $smallimage_attr_id . " AND entity_id=" . $entityid . ") OR  (`attribute_id`=" . $thumbnail_attr_id . " AND entity_id=" . $entityid . ")"
            );


            if (($recordIndex % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
                Mage::log('COMMIT COUNT :: ' . $recordIndex, Zend_Log::DEBUG, 'checkimagesetornot.log', true);
            }
            Mage::log('COUNT :: ' . $recordIndex, Zend_Log::DEBUG, 'checkimagesetornot.log', true);

        }
        Mage::log('DONE COUNT :: ' . $recordIndex, Zend_Log::DEBUG, 'checkimagesetornot.log', true);
        $writeAdapter->commit();

    } catch (Exception $e) {
        Mage::log("Exception -:" . $e->getMessage(), Zend_Log::DEBUG, 'checkimagesetornot.log', true);
        $writeAdapter->rollback();

    }
}

}
catch (Exception $e)
{
    Mage::log("Exception-:".$e->getMessage(),Zend_Log::DEBUG,'checkimagesetornot.log',true);
}

die("Done");

