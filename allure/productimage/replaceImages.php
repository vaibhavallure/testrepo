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
die("please enter sku ?sku=your_sku  And to apply changes ?sku=your_sku&apply=1  ");



$SKUlike=$_GET['sku'];



$apply=$_GET['apply'];
if(!isset($apply))
    $apply=0;



$newImageProductId=array();
$newImageAdded=0;

$media = Mage::getModel('catalog/product_attribute_media_api');
$ImageAddedflag=0;

$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('sku',array('like'=>$SKUlike.'%'));




try{


    foreach ($collection as $_product) {


        $product = Mage::getSingleton("catalog/product")->load($_product->getId());
        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

        $parent_sku = trim(current(explode("|", $product->getSku())));
        $delImageArray=array();

        foreach ($product->getMediaGalleryImages() as $image) {
            $newImageAdded=0;



              $imageName = trim(end(explode("/", $image->getFile())))."<br>";

            if (!is_numeric(strpos(strtoupper($imageName), strtoupper($parent_sku)))) {
                //Mage::log("start-----------------------------------------------------",Zend_Log::DEBUG,'replaceImage.log',true);

                Mage::log("start--- SKU = ".$product->getSku()." Wrong Image Found:= ".$image->getFile(),Zend_Log::DEBUG,'replaceImage.log',true);





                $delImageArray[]=array("pid"=>$product->getId(),"file"=>$image->getFile());

                $sku = str_replace(' ', '_', $product->getSku());
                $sku = str_replace('|', '-', $sku);

                $folder1 = substr($sku, 0, 1);
                $folder2 = substr($sku, 1, 1);

                $position = $image->getPosition();

                $folderPath = Mage::getBaseDir('media') . "/catalog/product/" . $folder1 . "/" . $folder2;

                $files = glob($folderPath . "/" . $sku . "_" . $position . "*.{jpg,gif,png}", GLOB_BRACE);


                if (count($files)) {
                    $fl = end($files);

                    Mage::log(" Image Found For Product SKu := ". $sku ." File=".$fl,Zend_Log::DEBUG,'replaceImage.log',true);

                    if($apply==1) {
                      $product->addImageToMediaGallery($fl, array('image', 'small_image', 'thumbnail'), false, false);

                         $newImageAdded=1;

                        Mage::log(" Image Added". $fl,Zend_Log::DEBUG,'replaceImage.log',true);

                    }


                }


               // Mage::log("end-----------------------------------------------------",Zend_Log::DEBUG,'replaceImage.log',true);


            }



            if($newImageAdded==1)
            {
                $newImageProductId[]=$product->getId();
            }

        }
        $product->save();


        if($apply==1) {

             foreach ($delImageArray as $delimg)
             {
                 if(count($delimg)) {

                         $media->remove($delimg['pid'], $delimg['file']);
                         Mage::log(" Image Removed := " . $delimg['file'] . "----end", Zend_Log::DEBUG, 'replaceImage.log', true);

                 }

             }

        }



    }



    

}
catch (Exception $e){
    Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'replaceImage.log',true);
}

echo "Done";
die;


