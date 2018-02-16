<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();


Mage::app()->setCurrentStore(0);
$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
echo "<pre>";


$lower = $_GET['lower'];
$upper= $_GET['upper'];
$stockId= $_GET['stock'];

if(empty($stockId)){
    die('Please add stock');
}
if($stockId==1){
    die("Can not do action for main store");
}
if(empty($lower) || empty($upper)){
    die('Please add Upper and Lower limit');
}


$productCollection = Mage::getModel('catalog/product')->getCollection()
->addAttributeToFilter('type_id', 'simple')
->addFieldToFilter('entity_id',
    array(
        'gteq' => $lower
    ))

->addFieldToFilter('entity_id', array(
    'lteq' => $upper
))
->load();

$recordIndex = 0;

$writeAdapter->beginTransaction();

foreach ($productCollection  as $product) {
    $oldItemInventory=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$stockId);
    if (!$oldItemInventory->getItemId()) {
        try {
            $oldItemInventory->setData('stock_id', $stockId);
            $oldItemInventory->setData('product_id', $product->getId());
            $oldItemInventory->setData('manage_stock', 1);
            $oldItemInventory->setData('qty', 0);
            $oldItemInventory->setData('is_in_stock', 1);
            $oldItemInventory->setData('backorders', 2);
            $oldItemInventory->setData('use_config_manage_stock', 0);
            $oldItemInventory->setData('min_sale_qty', 1);
            $oldItemInventory->setData('use_config_min_sale_qty', 0);
            $oldItemInventory->setData('max_sale_qty', 1000);
            $oldItemInventory->setData('use_config_max_sale_qty', 0);
            $oldItemInventory->save();
            Mage::log("Stock Id::".$stockId."--ProductId::". $product->getId(),Zend_log::DEBUG,'create_stock.log',true);
            if (($recordIndex % 250) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
            }
            $recordIndex += 1;
            
        } catch (Exception $e) {
            $writeAdapter->rollback();
            var_dump($e->getMessage());
        }
        
    }
}

$writeAdapter->commit();
echo "Done";