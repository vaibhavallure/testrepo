<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;
$recordIndex=0;
$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));

$resource = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');
$readConnection = $resource->getConnection('core_read');
$table = $resource->getTableName('catalog_product_relation');

$writeAdapter->beginTransaction();
foreach ($collection as $_product){
    $_product=Mage::getModel('catalog/product')->load($_product->getId());
    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
    $simple_collection = $conf->getUsedProductCollection();;
 
    foreach($simple_collection as $simple_product){
        $query='SELECT * FROM ' .$table .' where child_id='.$simple_product->getId();
        $results = $readConnection->fetchAll($query);
        
        if(count($results)<1){
            $query = 'INSERT INTO '.$table.' values('.$_product->getId().','.$simple_product->getId().')';
            Mage::log($recordIndex.'----'.$query,Zend_log::DEBUG,'relation.log',true);
            $recordIndex++;
            $writeAdapter->query($query);
            if (($recordIndex % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
                
            }
        }
    }
  
}
$writeAdapter->commit();
die("Finished");