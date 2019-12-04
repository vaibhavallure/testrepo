<?php

require_once ('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
//echo "<pre>";

$resource = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');
$readConnection = $resource->getConnection('core_read');


$fetchProduct="SELECT *  FROM `catalog_product_entity` WHERE `type_id` LIKE 'configurable' AND `sku` LIKE 'z%'";
$results = $readConnection->fetchAll($fetchProduct);

$i++;

foreach ($results as $res)
{
    $query1="SELECT * FROM `catalog_product_entity_text` WHERE `attribute_id` = 413 AND `entity_id` = {$res['entity_id']}";
    $results1 = $readConnection->fetchAll($query1);

    if(count($results1)) {
        $operation="UPDATE";
        $setQuery = "UPDATE `catalog_product_entity_text` SET `value`='3' WHERE `attribute_id`= 413 AND `entity_id`={$res['entity_id']}";
    }
    else {
        $operation="INSERT";
        $setQuery = "INSERT INTO `catalog_product_entity_text`(`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (4,413,0,{$res['entity_id']},'3')";
        }
//var_dump($results1);
    echo $i.":".$res['entity_id']."-".$res['sku']."-".$operation."\n";
    $writeAdapter->query($setQuery);
$i++;
}
