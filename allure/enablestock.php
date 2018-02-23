<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;
$lower = $_GET['lower'];
$upper= $_GET['upper'];
$stockId= $_GET['stockId'];

$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
$prodCount=0;

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
$collection->addAttributeToFilter('status',array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));

foreach ($collection->getAllIds() as $id){
   $stock= Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($id, $stockId);
   try {
       if(is_null($stock->getItemId())){
            var_dump($id);
            $data['stock_id'] = $stockId;
            $data['product_id'] = $id;
            $data['qty'] = 0;
            $data['manage_stock'] = 1;
            $data['use_config_manage_stock'] = 0;
            $data['min_sale_qty'] = 1;
            $data['use_config_min_sale_qty'] = 0;
            $data['max_sale_qty'] = 1000;
            $data['use_config_max_sale_qty'] = 0;
            $data['is_in_stock'] = 1;
            $data['use_config_backorders'] = 0;
            $data['backorders'] = 2;
            $stock->addData($data);
            $stock->save();
            $prodCount=$prodCount+1;
            Mage::log("Stock Id:".$stockId,Zend_log::DEBUG,'enable_stock.log',true);
            Mage::log("Product Id:".$id,Zend_log::DEBUG,'enable_stock.log',true);
            Mage::log("Product Count:".$prodCount,Zend_log::DEBUG,'enable_stock.log',true);
            if (($prodCount % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
            }
       }
   } catch (Exception $e) {
       var_dump($e->getMessage());
       break;
   }
}
Mage::log("Finished for Product Count:".$prodCount,Zend_log::DEBUG,'enable_stock.log',true);
$writeAdapter->commit();
