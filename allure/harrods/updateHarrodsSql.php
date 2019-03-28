<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$attribute_code = "harrods_inventory";
$attribute_details =
    Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
$attribute = $attribute_details->getData();
$attrbute_id=$attribute['attribute_id'];

if ($attrbute_id):

$resource = Mage::getSingleton('core/resource');

$writeAdapter = $resource->getConnection('core_write');
//$writeAdapter->beginTransaction();


$query="INSERT IGNORE INTO catalog_product_entity_varchar(entity_id,entity_type_id,attribute_id) 
SELECT en.entity_id,4,{$attrbute_id} from catalog_product_entity en where en.type_id = 'configurable'
";
    $writeAdapter->query($query);

    try {
        $writeAdapter->commit();
    }catch (Exception $e)
    {

        Mage::log("Exception -:".$e->getMessage(),Zend_Log::DEBUG,'update_harrods_inventory.log',true);


    }


$query = "update catalog_product_entity_varchar a INNER JOIN(
select link.product_id,link.parent_id,sum(varc.value) as stock from catalog_product_super_link link 
join catalog_product_entity_varchar varc on (link.product_id = varc.entity_id and varc.attribute_id = {$attrbute_id})
group by link.parent_id 
 ) b
set a.value =  b.stock
where a.entity_id = b.parent_id and a.attribute_id = {$attrbute_id}";

$writeAdapter->query($query);

try {
    $writeAdapter->commit();

}catch (Exception $e)
{
    Mage::log("Exception -:".$e->getMessage(),Zend_Log::DEBUG,'update_harrods_inventory.log',true);

}
endif;
