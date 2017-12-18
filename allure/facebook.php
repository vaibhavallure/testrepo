<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
$limit   = $_GET['limit'];
$type   = isset($_GET['type'])?$_GET['type']:false;

if(empty($limit))
    $limit=100;

$csvFile = 'facebook';



if ($type!='csv'){
header('Content-Disposition: attachment; filename='.$csvFile.'.csv');
header('Content-type: text/csv');
header('Pragma: no-cache');
header('Expires: 0');
}else{
    header('Content-type: text/plain');
}
$file = fopen('php://output', 'w');

fputcsv($file, array('id', 'title', 'description', 'availability', 'condition','price','link','image_link','additional_image_link','shipping_weight','brand','product_type'));
$data = array();

$collection=Mage::getModel("catalog/product")->getCollection();
$collection->addAttributeToFilter('type_id', 'configurable');
$collection->getSelect()->limit($limit);

$imageHelper = Mage::helper('catalog/image');

foreach ($collection as $_product){
 
    $product=Mage::getModel("catalog/product")->load($_product->getId());
    $productId=$product->getId();
    $name=$product->getName();
    $cat=getProductCategories($_product->getCategoryIds()); 
    $description=$product->getDescription();
    $stockStatus="in stock";
    $condition="new";
    $price=round($product->getPrice(),2);
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
    $weight=$product->getWeight() ."lb";
    $data[]=array($productId,$name,$description,$stockStatus,$condition,$price,$link,$imageMain,$additional_image_link,$weight,'Maria Tash',$cat);
}
foreach ($data as $row)
{
    fputcsv($file, $row);
}

function getProductCategories($ids){
    
    $categories=Mage::getModel('catalog/category')->getCollection()->addFieldToFilter('entity_id',array("in"=>$ids));
    if ($categories)
    {
        foreach ($categories as $category)
        {
            
            $path        = '';
            $pathInStore = $category->getPathInStore();
            $pathIds     = array_reverse(explode(',', $pathInStore));
            array_shift($pathIds);
            array_shift($pathIds);
           /*  print_r($pathIds);
            die; */
            $categories = $category->getParentCategories();
            
            foreach ($pathIds as $key => $categoryId) {
                if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    
                    $path .= $categories[$categoryId]->getName();
                    if($key != array_pop(array_keys($pathIds))){
                        $path.= ' > ';
                    }
                }
            }
            /* if ($path)
            {
                $path = substr($path, 0, -1);
               
            } */
            
            $categoriesHtml .= $path;
            $categoriesHtml .=',';
        }
    }
    return $categoriesHtml;
}

exit();

//die("Finish");
