<?php
/**
check label present or not for image
 *
 */



require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$count=0;

if(!isset($_GET['sku']))
die("please enter sku ?sku=your_sku ");



$SKUlike=$_GET['sku'];




$folderPath   = Mage::getBaseDir('var') . DS . 'export';
$date = date('Y-m-d');
$filename     = "image_label_report_".$date.".csv";
$filepath     = $folderPath . DS . $filename;

$io = new Varien_Io_File();
$io->setAllowCreateFolders(true);
$io->open(array("path" => $folderPath));
$csv = new Varien_File_Csv();



$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('sku',array('like'=>$SKUlike.'%'));
$rowData = array();
$header=array(
    "sku"=>"SKU",
    "img1"=>"Image 1",
    "img2"=>"Image 2",
    "img3"=>"Image 3",
    "img4"=>"Image 4",
    "img5"=>"Image 5",
    "img6"=>"Image 6",
    "img7"=>"Image 7",
    "img8"=>"Image 8",
    "img9"=>"Image 9",
    "img10"=>"Image 10"
);

$rowData[]=$header;

try{


    foreach ($collection as $_product){


    $product = Mage::getSingleton("catalog/product")->load($_product->getId());
    $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

        $parent_sku=trim(current(explode("|", $product->getSku())));

        $imagesArray = array();
        $i=1;

        foreach ($product->getMediaGalleryImages() as $image) {

          if(!$image->getLabel())
          {

              if($i==1)
              {
                  $imagesArray["sku"]=$product->getSku();
              }

             $imagesArray["img".$i]=$image->getFile();

              $i++;


          }

        }

        if(count($imagesArray))
        $rowData[]=$imagesArray;
    }




   $csv->saveData($filepath,$rowData);
//



    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }



}
catch (Exception $e){
    Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'find_image_with_no_label.log',true);
}

echo "Done";
die;


