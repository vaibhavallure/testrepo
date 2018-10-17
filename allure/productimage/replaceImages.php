<?php
/**
 *this script find out images with different name than its product sku and replace original one
 * using SKU
 */



require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$count=0;

if(!isset($_GET['sku']))
die("please enter sku ?sku=your_sku ");



$SKUlike=$_GET['sku'];

$newImageProductId=array();
$newImageAdded=0;

$media = Mage::getModel('catalog/product_attribute_media_api');
$ImageAddedflag=0;

$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('sku',array('like'=>$SKUlike.'%'));




try{


    foreach ($collection as $_product) {


        $product = Mage::getModel("catalog/product")->load($_product->getId());
        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

        $parent_sku = trim(current(explode("|", $product->getSku())));


        foreach ($product->getMediaGalleryImages() as $image) {


              $imageName = trim(end(explode("/", $image->getFile())))."<br>";

            if (!is_numeric(strpos(strtoupper($imageName), strtoupper($parent_sku)))) {
                Mage::log("start-----------------------------------------------------",Zend_Log::DEBUG,'replaceImage.log',true);

                Mage::log("Wrong Image Found:= ".$image->getFile(),Zend_Log::DEBUG,'replaceImage.log',true);


                $sku = str_replace(' ', '_', $product->getSku());
                $sku = str_replace('|', '-', $sku);

                $folder1 = substr($sku, 0, 1);
                $folder2 = substr($sku, 1, 1);

                $position = $image->getPosition();

                $folderPath = Mage::getBaseDir('media') . "/catalog/product/" . $folder1 . "/" . $folder2;

                $files = glob($folderPath . "/" . $sku . "_" . $position . "*.{jpg,gif,png}", GLOB_BRACE);


                if (count($files)) {
                    $fl = end($files);

                    Mage::log(" Image Found For Product SKu := ". $fl,Zend_Log::DEBUG,'replaceImage.log',true);


                  //  $product->addImageToMediaGallery($fl, array('image', 'small_image', 'thumbnail'), false, false);
                   // $product->save();

                  $newImageAdded=1;

                    Mage::log(" Image Added". $fl,Zend_Log::DEBUG,'replaceImage.log',true);


                }

               // $media->remove($product->getId(),$image->getFile());

                Mage::log(" Image Removed := ". $image->getFile(),Zend_Log::DEBUG,'replaceImage.log',true);

                Mage::log("end-----------------------------------------------------",Zend_Log::DEBUG,'replaceImage.log',true);


            }



            if($newImageAdded==1)
            {
                $newImageProductId[]=$product->getId();
            }

        }


    }



//    if(count($newImageProductId)) {
//
//        foreach ($newImageProductId as $pid) {
//            $product = Mage::getModel("catalog/product")->load($pid);
//
//            foreach ($product->getMediaGalleryImages() as $image) {
//
//                $key = substr(end(explode("/", $image->getFile())), strlen($product->getSku()) + 1, 1);
//              //  echo $image->getFile() . "<br>";
//                $backend = $attributes['media_gallery']->getBackend();
//                $backend->updateImage($product, $image->getFile(), array('position' => $key, 'label' => $product->getName() . ' Image #' . $key));
//
//                if ($key == 1) {
//                    $value = $image->getFile();
//                    Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()),
//                        array(
//                            'image' => $value,
//                            'small_image' => $value,
//                            'thumbnail' => $value,
//                        ),
//                        0);
//                }
//            }
//
//            $product->save();
//            Mage::log("new image updated",Zend_Log::DEBUG,'replaceImage.log',true);
//
//        }
//    }


}
catch (Exception $e){
    Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'replaceImage.log',true);
}

echo "Done";
die;


