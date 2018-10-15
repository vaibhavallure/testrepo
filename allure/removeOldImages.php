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
    die("please enter sku ?sku=your_sku and if want to update label of all products use ?sku=your_sku&updatelabel=1");

$updatelabel=0;
$replace=0;


$SKUlike=$_GET['sku'];
$updatelabel=$_GET['updatelabel'];
$replace=$_GET['replace'];



$productModel=Mage::getModel('catalog/product');
$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('sku',array('like'=>$SKUlike.'%'));



try{

    $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
    $media = Mage::getModel('catalog/product_attribute_media_api');


    foreach ($collection as $_product){
        $files=array();
        $newFiles=array();
        $oldFiles=array();

        $product = Mage::getModel("catalog/product")->load($_product->getId());
        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

        foreach ($product->getMediaGalleryImages() as $image) {

            $imageNumber = substr(end(explode("/", $image->getFile())), strlen($product->getSku()) + 1, 1);

            if (is_numeric($imageNumber)) {
                $files[$imageNumber][] = array("file" => $image->getFile(),
                    "id" => $image->getId(),
                    "label" => $image->getLabel(),
                    "path" => $image->getPath(),
                    "position" => $image->getPosition()
                );

            }
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
            else if(count($fl)==1) {
                if($updatelabel==1) {
                    if (!$fl[0]['label']) {
                        $backend = $attributes['media_gallery']->getBackend();
                        $backend->updateImage($product, $fl[0]['file'], array('label' => $product->getName() . ' Image #' . $key));

                        Mage::log("(".$product->getSku().") label updated for image position ".$key." ",Zend_Log::DEBUG,'remove_old_images.log',true);

                    }
                    else{
                        Mage::log("(".$product->getSku().") label found for image position ".$key." ",Zend_Log::DEBUG,'remove_old_images_label_not_found.log',true);
                    }
                }
            }
        }

        if(count($newFiles))
        {
            Mage::log("Duplicate images found {$product->getSku()} for position= " .implode(", ",array_keys($newFiles)), Zend_Log::DEBUG, 'remove_old_images.log', true);
        }
        else {
            if ($replace) {
                Mage::log("duplicate images not found for" . $product->getSku(), Zend_Log::DEBUG, 'remove_old_images_not_found.log', true);
            }
        }



        if($replace) {

            if (count($newFiles)) {
                foreach ($newFiles as $key => $nf) {

                    foreach ($nf as $n) {
                        $backend = $attributes['media_gallery']->getBackend();
                        $backend->updateImage($product, $n['file'], array('position' => $key, 'label' => $product->getName() . ' Image #' . $key));

                        if ($key == 1) {
                            $value = $n['file'];
                            Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()),
                                array(
                                    'image'=>$value,
                                    'small_image'=>$value,
                                    'thumbnail'=>$value,
                                ),
                                0);
                        }

                    }
                }
            }



            if (count($oldFiles)) {
                foreach ($oldFiles as $key => $ol) {
                    foreach ($ol as $o) {
                        $media->remove($product->getId(), $o['file']);
                    }
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


