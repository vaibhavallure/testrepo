<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$count=0;

$productModel=Mage::getModel('catalog/product');
$collecton=Mage::getModel('catalog/product')->getCollection();
$collecton->addAttributeToFilter('sku',array('like'=>'c%'));

$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
echo "<pre>";
try{
    $connection->beginTransaction();
    $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
    foreach ($collecton as $_product){
        $product = Mage::getModel("catalog/product")
        ->load($_product->getId());
        
        $sku = $product->getSku();
        $x_sku = 'X'.substr($sku, 1);
        $x_ProductId = Mage::getModel("catalog/product")->getIdBySku($x_sku);
        if($x_ProductId){
            $x_Product = Mage::getModel("catalog/product")->load($x_ProductId);
            Mage::log($count.'.'."source sku-".$product->getSku()."=="."target sku-".$x_Product->getSku()
                ,Zend_Log::DEBUG,'copy_image_view',true);
            foreach ($x_Product->getMediaGalleryImages() as $image){
                $path = $image['path'];
                if(file_exists($path)){
                    try{
                        Mage::getSingleton('catalog/product_action')->updateAttributes(array($x_ProductId), array('image'=>$image['file'],'small_image'=>$image['file'],'thumbnail'=>$image['file']), 0);
                        break;
                    }catch (Exception $e){
                        Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'copy_image_view',true);
                    }
                }
            }
           
            $count++;
            if($count%10==0){
                if($count==45)
                    break;
                $connection->commit();
                $connection->beginTransaction();
            }
        }
    }
    $connection->commit();
    Mage::log("total x sku product -:".$count,Zend_Log::DEBUG,'copy_image_view',true);
}catch (Exception $e){
    $connection->rollback();
    Mage::log("Exception 2-:".$e->getMessage(),Zend_Log::DEBUG,'copy_image_view',true);
}
echo "Done";
die;

