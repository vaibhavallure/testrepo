<?php
require_once ('../app/Mage.php');
umask(0);
Mage::app();
$collection = Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect('*');
$count=0;
$write = Mage::getSingleton('core/resource')->getConnection('core_write');
foreach ($collection as $productCollection) {
    $product = mage::getModel('catalog/product')->load($productCollection->getId());
    $images = $product->getMediaGallery('images');
    foreach ($images as $image) {
        //print_r($image);
      // echo $image['file']."\n";
        $file=explode('/', $image['file']);
        $imageName=$file[count($file)-1];
       // echo $file[count($file)-1]."\n";
        $firstLetter=substr($imageName,0,1);
        $firstLetterSku=substr($product->getSku(),0,1);
        if ($firstLetterSku == 'X' || $firstLetterSku == "x") {
            if (strtolower($firstLetter) != strtolower($firstLetterSku)) {
                array_pop($file);
                
                $newImageFile = 'X' . substr($imageName, 1);
                
                $newImage = implode('/', $file);
                $newImageLocation = $newImage . '/' .  'X' . substr($imageName, 1);
                $oldImageLocation = $newImage . '/' .  $imageName;
                
                copy('../media/catalog/product' . $oldImageLocation, '../media/catalog/product' . $newImageLocation);
                
                if(file_exists('../media/catalog/product'.$oldImageLocation)){
                   
                   
                    if (file_exists('../media/catalog/product' . $newImageLocation)) {
                        
                        $query = 'UPDATE catalog_product_entity_media_gallery SET value = "' . $newImageLocation . '" WHERE value ="' . $oldImageLocation . '"';
                        $write->query($query);
                        
                        $query = 'UPDATE catalog_product_entity_varchar SET value = "' . $newImageLocation . '" WHERE value ="' . $oldImageLocation . '"';
                        $write->query($query);
                        $count ++;
                        echo $count . "-" . $product->getSku() . "============" . $oldImageLocation ."============".$newImageLocation. "\n";
                        
                        Mage::log($count . "-" . $product->getSku() . "============" . $oldImageLocation ."============".$newImageLocation,Zend_log::DEBUG,'copyimages.log',true);
                    }
                
                }
                
            }
        }
        //break;
    }
    //break;
}
die("Finished");