<?php
/**
 *this script remove old images from product and set new images prior position
 * using SKU
 */

require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$count=0;

if(!isset($_GET['sku']))
die("please enter sku");


$SKUlike=$_GET['sku'];


$productModel=Mage::getModel('catalog/product');
$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('sku',array('like'=>$SKUlike.'%'));



try{

    $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
    $media = Mage::getModel('catalog/product_attribute_media_api');

    $files=array();
    $newFiles=array();
    $oldFiles=array();

    foreach ($collection as $_product){


    $product = Mage::getModel("catalog/product")->load($_product->getId());
    $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

        foreach ($product->getMediaGalleryImages() as $image) {

           $imageNumber = substr(end(explode("/",$image->getFile())), strlen($product->getSku())+1, 1);

           $files[$imageNumber][]=array("file"=>$image->getFile(),
               "id"=>$image->getId(),
               "label"=>$image->getLabel(),
               "path"=>$image->getPath(),
               "position"=>$image->getPosition()
               );

        }



         foreach ($files as $key=>$fl)
         {

             if(count($fl)==2)
             {
                 if($fl[0]['id']>$fl[1]['id'])
                 {
                    $newFiles[$key][]=$fl[0];
                    $oldFiles[$key][]=$fl[1];
                 }
                 else
                 {
                     $newFiles[$key][]=$fl[1];
                     $oldFiles[$key][]=$fl[0];
                 }

             }

         }

        if(count($newFiles))
        {
            Mage::log("Duplicate images found {$product->getSku()} for position= " .implode(", ",array_keys($newFiles)), Zend_Log::DEBUG, 'remove_old_images.log', true);
        }
        else {
            Mage::log("dulplicate not images found for" . $product->getSku(), Zend_Log::DEBUG, 'remove_old_images.log', true);
        }

        if(count($newFiles)) {
             foreach ($newFiles as $key => $nf) {
                 foreach ($nf as $n) {
                     $backend = $attributes['media_gallery']->getBackend();
                     $backend->updateImage($product, $n['file'], array('position' => $key, 'label' => $product->getName() . ' Image #' . $key));
                 }
             }
         }

        $product->save();

        if(count($oldFiles)) {
            foreach ($oldFiles as $key => $ol) {
                foreach ($ol as $o) {
                    $media->remove($product->getId(), $o['file']);
                }
            }
        }



    }



}
catch (Exception $e){
    Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'remove_old_images.log',true);
}

echo "Done";
die;

