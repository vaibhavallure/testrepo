<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;
$lower = $_GET['lower'];
$upper= $_GET['upper'];

Mage::getModel("allure_teamwork/observer")->addCpCustomerIntoMagento();

die;

if(empty($lower) || empty($upper)){
    die('Please add Upper and Lower limit');
}


$app = Mage::app('default');

Mage::getSingleton('core/session', array('name' => 'adminhtml'));

Mage::app()->setCurrentStore(0);


$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
$table        = $resource->getTableName('catalog/product_super_attribute');

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('entity_id',
    array(
        'gteq' => $lower
    ));

$collection->addAttributeToFilter('entity_id', array(
    'lteq' => $upper
));
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));




foreach ($collection as $prod){
    $productId  = $prod->getId();
    $_product = Mage::getModel('catalog/product')->load($productId);
    $mainSku = $_product->getSku();
    $mainName = $_product->getName();
    
    $productAttributeOptions = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
    echo "<pre>";
    //print_r($productAttributeOptions);
    
    $childProducts = Mage::getModel('catalog/product_type_configurable')
    ->getUsedProducts(null,$_product);
    
    $atributeCode = 'metal_color';
    $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
    $options = $attribute->getSource()->getAllOptions();
    
    $colorArr = array();
    foreach ($options as $option){
        $val = explode(" ", $option['label']);
        $str = $option['label'];
        if(count($val)>2)
            $str = $val[0]." ".$val[count($val)-1];
            
            $colorArr[$option['value']]=$str;
    }
    
    
    $multipleOptions=array();
    foreach ($productAttributeOptions as $prodOptions){
        if($prodOptions['attribute_code']!='metal_color'){
            $optionsArray=array();
            
            //foreach ($_product->getOptions() as $option) {
            /* if ($option->getTitle() === 'Post Length'){
             echo 'Product '. $_product->getName() . ' has a Post Length option!<br>';
             }else{ */
            foreach ($prodOptions['values'] as $optionsValues){
                $value=array(
                    'title' => $optionsValues['label'],
                    'price' => 00.00,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0,
                );
                array_push($optionsArray,$value);
            }
            
            $customOptions=array(
                'title' => $prodOptions['label'],
                'type' => 'drop_down',
                'is_required' => 1,
                'sort_order' => 0,
                'values' => $optionsArray
            );
            array_push($multipleOptions,$customOptions);
            $query        = "delete from {$table} WHERE product_super_attribute_id =".$prodOptions['id'];
            $writeAdapter->query($query);
            //}
            //}
        }
        
}

$product = Mage::getModel('catalog/product')->load($productId);
$product->setProductOptions($multipleOptions);
$product->setCanSaveCustomOptions(true);
$product->save();

echo $productId ." - Option Added successfully<br>";



$websitesCollection = Mage::getModel("core/website")->getCollection()
->addFieldToFilter('stock_id',array('neq'=>0));
$websiteArr = array();
foreach ($websitesCollection as $website){
    $websiteArr[$website->getId()] = $website->getStockId();
}

$mainArr = array();
foreach ($options as $prodOptions){
    $count = 0;
    $value = $prodOptions['value'];
    $inventory = array();
    $productId = 0;
    foreach ($childProducts as $child){
        if($value==$child->getMetalColor()){
            $count+=1;
            $websiteIds = $child->getWebsiteIds();
            foreach ($websiteIds as $websiteId){
                $stock = Mage::getModel('cataloginventory/stock_item')
                ->loadByProductAndStock($child,$websiteArr[$websiteId]);
                $inventory[$websiteId] += $stock->getQty();
            }
            
            if($count==1){
                $productId = $child->getId();
            }
        }
    }
    if($productId!=0)
        $mainArr[$productId] = $inventory;
        
}

//var_dump($mainArr);

foreach ($childProducts as $child){
    $productId = $child->getId();
    $product = Mage::getModel('catalog/product')->load($productId);
    $sku = "";
    $name = "";
    if(array_key_exists($productId, $mainArr)){
        $sku = $mainSku."|".$colorArr[$child->getMetalColor()];
        $name = $mainName."-".$colorArr[$child->getMetalColor()];
        $product->setName($name);
        $product->setSku($sku)->save();
        foreach ($mainArr[$productId] as $stockId=>$qty){
            $stock = Mage::getModel('cataloginventory/stock_item')
            ->loadByProductAndStock($child,$stockId);
            $stock->setQty($qty)->save();
        }
    }else{
        $product->delete();
    }
}

echo "Operation Successfull";
}
die;


die;



Mage::getModel('appointments/cron')->autoProcess();
die;

Mage::getModel('allure_instacatalog/cron')->syncFeeds();
die;
$app = Mage::app('default');

Mage::getSingleton('core/session', array('name' => 'adminhtml'));

Mage::app()->setCurrentStore(0);

//var_dump(Mage::app()->getStore()->isAdmin());

$findify_cron = Mage::getModel('findify/cron_daily');
ini_set('memory_limit', '-1');
$findify_cron->runFeed(1);

die;

if($ch==1){
    Mage::getModel('productshare/observer')->shareAvailableProductsToStoreRun();
    
}else {
    if (isset($_GET['products']) && !empty($_GET['products']))
        $products = explode(',', $_GET['products']);
        $website = $_GET['website'];
        $storeId = $_GET['store'];
        Mage::getModel('productshare/observer')->shareAvailableProductsToStoreAdditional($products,$storeId,$website);
        
}

/* foreach ($collection as $item){
 if($item)
 print_r( $item);
 else
 echo "bye";
 } */
 die;
 function getWebsiteIds(){
     $websiteIds = array();
     foreach (Mage::app()->getWebsites() as $website) {
         $websiteIds[] = $website->getId();
     }
     return $websiteIds;
 }
 
 $collection = Mage::getModel('catalog/product')->getCollection();
 //$websiteIds = getWebsiteIds();
 $websiteId = 2;
 $storeId = 2;
 foreach($collection as $_product):
 try{
     $product = Mage::getModel('catalog/product')->load($_product->getId());
     $websiteIds = $product->getWebsiteIds();
     $storeIds = $product->getStoreIds();
     if (!in_array($websiteId, $websiteIds)) {
         array_push($websiteIds,$websiteId);
         array_unique($websiteIds);
         if(!in_array($storeId, $storeIds)){
             array_push($storeIds,$storeId);
             array_unique($storeIds);
             $product->setStoreIds($storeIds);
         }
         $product->setWebsiteIds($websiteIds)->save();
     }
 }catch(Exception $e){
     Mage::log($e->getMessage());
     Mage::log($e->getMessage(),Zend_log::DEBUG,'multistore_copy_product.log',true);
 }
 endforeach;
 