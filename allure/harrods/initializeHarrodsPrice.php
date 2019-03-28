<?php
/**
 * Created by allure.
 * User: adityagatare
 * Date: 12/11/18
 * Time: 7:19 PM
 *
 * this script run once only when we want to initialize allure_harrodsinvenotry_price table
 */



require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$attribute_code = "harrods_price";
$attribute_details =
    Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
 $attribute = $attribute_details->getData();
 $attrbute_id=$attribute['attribute_id'];





    $resource = Mage::getSingleton('core/resource');
    $writeAdapter = $resource->getConnection('core_write');
    $readConnection = $resource->getConnection('core_read');


    $query="SELECT * FROM `catalog_product_entity_decimal` WHERE `attribute_id` = {$attrbute_id}";

    $results = $readConnection->fetchAll($query);

    foreach ($results as $res)
    {

        if($res['value']) {
            $curruntDate = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');

            $insertQuery = "INSERT INTO `allure_harrodsinventory_price`(`productid`, `price`,  `updated_date`) VALUES ({$res['entity_id']},{$res['value']},'" . $curruntDate . "')";

            try {
                $writeAdapter->query($insertQuery);
                $writeAdapter->commit();


            } catch (Exception $e) {
                Mage::log("Exception -:" . $e->getMessage(), Zend_Log::DEBUG, 'harrodsinventory_price_table_update.log', true);

            }
        }

    }

die("Done");

