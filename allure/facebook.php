<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
$limit   = $_GET['limit'];
if(empty($limit))
    $limit=100;

$csvFile = 'facebook';

header('Content-type: text/csv');
// header("'Content-Disposition: attachment; filename='.$csvFile.'");
header('Content-Disposition: attachment; filename='.$csvFile.'.csv');

// do not cache the file
header('Pragma: no-cache');
header('Expires: 0');
$file = fopen('php://output', 'w');

fputcsv($file, array('id', 'title', 'description', 'availability', 'condition','price','link','image_link','additional_image_link','product_type','shipping_weight'));
$data = array();

$collection=Mage::getModel("catalog/product")->getCollection();
$collection->addAttributeToFilter('type_id', 'configurable');
$collection->getSelect()->limit($limit);

$imageHelper = Mage::helper('catalog/image');

foreach ($collection as $_product){
 
    $product=Mage::getModel("catalog/product")->load($_product->getId());
    $productId=$product->getId();
    $name=$product->getName();
    $description=$product->getDescription();
    $stockStatus="in stock";
    $condition="new";
    $price=$product->getPrice();
    $link='https://www.venusbymariatash.com/'.$product->getUrlKey().'.html';
   // $image= $imageHelper->init($product, 'thumbnail'); 
 //   $thumbnail=Mage::helper('catalog/image')->init($product, 'thumbnail');
    $imageMain=Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail());;
    $brand="Maria Tash";
    $imgAray=array();
    foreach ($product->getMediaGalleryImages() as $image) {
        $imgAray[]= $image->getUrl();
    }  
    if(empty($imageMain))
        $imageMain=$imgAray[0];
    $additional_image_link=implode(',', $imgAray);
    $product_type="configurable";
    $weight=$product->getWeight();
    $data[]=array($productId,$name,$description,$stockStatus,$condition,$price,$link,$imageMain,$additional_image_link,$product_type,$weight);
}
foreach ($data as $row)
{
    fputcsv($file, $row);
}

exit();

die("Finish");
