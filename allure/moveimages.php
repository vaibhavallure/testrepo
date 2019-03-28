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
        $file=explode('/', $image['file']);
        $imageName=$file[count($file)-1];
        $firstLetter=substr($imageName,0,1);
        $firstLetterSku=substr($product->getSku(),0,1);
        if (strtolower($firstLetter) == x) {
            
            if (strtolower($file[1]) != 'c')
                continue;
            $newImage = implode('/', $file);
            // echo $newImage.'\n';
            
            $oldImageLocation = $newImage;
            if ($file[1] == 'C') {
                $newImageLocation = 'X' . substr($newImage, 2);
            } elseif ($file[0] == 'c') {
                $newImageLocation = 'x' . substr($newImage, 2);
            }
            
            $newImageLocation = '/' . $newImageLocation;
            echo "OLD:: " . $image['file'] . "\n";
            echo "NEW:: " . $newImageLocation . "\n";
            copy('../media/catalog/product' . $oldImageLocation, '../media/catalog/product' . $newImageLocation);
            
            if (file_exists('../media/catalog/product' . $newImageLocation)) {
                
                $query = 'UPDATE catalog_product_entity_media_gallery SET value = "' . $newImageLocation . '" WHERE value ="' . $oldImageLocation . '" AND entity_id="' . $product->getId() . '"';
                $write->query($query);
                
                $query = 'UPDATE catalog_product_entity_varchar SET value = "' . $newImageLocation . '" WHERE value ="' . $oldImageLocation . '" AND entity_id="' . $product->getId() . '"';
                $write->query($query);
                $count ++;
                echo $count . "-" . $product->getSku() . "============" . $oldImageLocation . "============" . $newImageLocation . "\n";
                
                Mage::log($count . "-" . $product->getSku() . "============" . $oldImageLocation . "============" . $newImageLocation, Zend_log::DEBUG, 'copyimages.log', true);
            }
        }
      }
}
        //break;
    //break;
die("Finished");