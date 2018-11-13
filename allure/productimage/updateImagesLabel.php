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


$attribute_code = "name";
$attribute_details =
    Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
$attribute = $attribute_details->getData();
 $attrbute_id=$attribute['attribute_id'];





    $resource = Mage::getSingleton('core/resource');
    $writeAdapter = $resource->getConnection('core_write');
    $readConnection = $resource->getConnection('core_read');


    $query="SELECT * FROM `catalog_product_entity_media_gallery` gal JOIN `catalog_product_entity_media_gallery_value` val ON gal.value_id=val.value_id WHERE label IS null  ORDER BY VALUE DESC";

    $results = $readConnection->fetchAll($query);

    foreach ($results as $res)
    {
        $product_id=$res["entity_id"];




        $query1="SELECT `value` FROM `catalog_product_entity_varchar` WHERE `entity_id`={$product_id} AND `attribute_id`={$attrbute_id}";
        $productname = current($readConnection->fetchAll($query1))['value'];



        if(strpos($res["value"], 'model')!=false)
        {
           $pos='#model';
        }
        else {
            $pos = null;
            foreach (array_reverse(explode("_", $res["value"])) as $ar) {
                if (ctype_digit($ar)) {
                    $pos = '#'.$ar;
                } else {
                    if ($pos != null) {
                        break;
                    }
                }
            }
        }


         $label=$productname." Image ".$pos;
         $updateQuery="UPDATE `catalog_product_entity_media_gallery_value` SET `label`='{$label}' WHERE `value_id`={$res['value_id']}";





        try {
            $writeAdapter->query($updateQuery);
            $writeAdapter->commit();
            Mage::log("Label Updated For value_id=".$res['value_id'],Zend_Log::DEBUG,'imagelabelupdate.log',true);


        }catch (Exception $e)
        {
            Mage::log("Exception -:".$e->getMessage(),Zend_Log::DEBUG,'imagelabelupdate.log',true);

        }


    }

die("Done");

