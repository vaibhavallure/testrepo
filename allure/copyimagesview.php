<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$lower = $_GET['lower'];
$upper= $_GET['upper'];

if(empty($lower) || empty($upper)){
    die('Please add Upper and Lower limit');
}

$count=0;

$productModel=Mage::getModel('catalog/product');
$collecton=Mage::getModel('catalog/product')->getCollection();
$collecton->addAttributeToFilter('entity_id', array(
    'from' => $lower,
    'to' => $upper
));
$collecton->addAttributeToFilter('sku',array('like'=>'C%'));


$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
//echo "<pre>";
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
            
            $cimagePath=$product->getData('image');
            $ext = substr($xcimagePath, strrpos($cimagePath, "."));
            $cimagePath=str_replace($ext, "", $cimagePath);
            
            $csmallPath=$product->getData('small_image');
            $ext = substr($csmallPath, strrpos($csmallPath, "."));
            $csmallPath=str_replace($ext, "", $csmallPath);
            
            $cthumbnailPath=$product->getData('thumbnail');
            $ext = substr($cthumbnailPath, strrpos($cthumbnailPath, "."));
            $cthumbnailPath=str_replace($ext, "", $cthumbnailPath);
           // echo $cimagePath;
            
           // print_r($product->getSku()."-----".$product->getData('image'));
          //  echo "<br>";
         
                
            foreach ($x_Product->getMediaGalleryImages() as $image){
                $xpath = $image['file'];
                $ext = substr($xpath, strrpos($xpath, "."));
                $xpath=str_replace($ext, "", $xpath);
              //  echo $x_sku.'------------'.$xpath;
              //  echo "<br>";
                    try{
                        if($xpath==$cimagePath.'_1' || $xpath==$cimagePath.'_2')
                           Mage::getSingleton('catalog/product_action')->updateAttributes(array($x_ProductId), array('image'=>$image['file']), 0);
                        if($xpath==$csmallPath.'_1' || $xpath==$csmallPath.'_2')
                           Mage::getSingleton('catalog/product_action')->updateAttributes(array($x_ProductId), array('small_image'=>$image['file']), 0);
                        if($xpath==$cthumbnailPath.'_1' || $xpath==$cthumbnailPath.'_2')
                           Mage::getSingleton('catalog/product_action')->updateAttributes(array($x_ProductId), array('thumbnail'=>$image['file']), 0);
                       // break;
                    }catch (Exception $e){
                        Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'copy_image_view',true);
                    }
                
            }
               // print_r($product->getData('image'));
            
           
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

