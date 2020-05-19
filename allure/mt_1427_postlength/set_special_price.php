<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);


/*
XTHBF
XTHBF6
XTHD4
XTHD2
ZPBF6R0
XTHBF25D
XTHBF2D
XTHMQD
*/
/*Get group Ids*/
Mage::log('------------- Start ------------',Zend_Log::DEBUG,'setPostPrice.log',true);
$attrCode = 'allowed_group';
$sourceModel = Mage::getModel('catalog/product')->getResource()
    ->getAttribute($attrCode)->getSource();
$valuesText = explode(',', 'Wholesale');
$valuesIds = array_map(array($sourceModel, 'getOptionId'), $valuesText);

/*
 *General
 *NOT LOGGED IN
 * */
$groupPricingData = array(
    array ('website_id'=>0, 'cust_group'=>0, 'price'=>0),
    array ('website_id'=>0, 'cust_group'=>1, 'price'=>0)
);


$sku_list = array('XTHBF',
    'XTHBF6',
    'XTHD4',
    'XTHD2',
    'ZPBF6R0',
    'XTHBF25D',
    'XTHBF2D',
    'XTHMQD');

$totalCount = 0;
foreach ($sku_list as $sku) {
    $collection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('sku', $sku)
        ->addAttributeToFilter('type_id', 'configurable');
    $foundCount = $collection->getSize();

    if ($foundCount > 0) {
        $childrenCount = 0;
        $product = $collection->getFirstItem();
        $parentId = $product->getId();

        Mage::log('Parent SKU: '.$sku,Zend_Log::DEBUG,'setPostPrice.log',true);
        Mage::log('Parent Id. :'.$parentId,Zend_Log::DEBUG,'setPostPrice.log',true);

        /*Set Allowed group to parent*/
        $parentProduct = Mage::getModel('catalog/product')
            ->load($parentId);

        $priceData = getPriceArray($parentProduct,$groupPricingData);
        savePrice($parentProduct,$priceData);
        $totalCount++;
        $childrenProducts = Mage::getModel('catalog/product_type_configurable')
            ->getChildrenIds($parentId);
        $cnt = 0;
        foreach ($childrenProducts as $set) {
            foreach ($set as $child) {
                /*Set Allowed group to childs*/
                $childProduct = Mage::getModel('catalog/product')
                    ->load($child);
                if ($childProduct) {
                    $priceData = getPriceArray($childProduct,$groupPricingData);
                    savePrice($childProduct,$priceData);
                    $totalCount++;
                }
                $childrenCount++;
            }
        }
        Mage::log('Total Childrens: '.$childrenCount,Zend_Log::DEBUG,'setPostPrice.log',true);
        echo PHP_EOL . ' Child Count' . $cnt;
    } else {
        Mage::log('NOT FOUND :'.$sku,Zend_Log::DEBUG,'setPostPrice.log',true);
        echo "Product Not Found...!";
    }
}
Mage::log('Total Found: '.$totalCount,Zend_Log::DEBUG,'setPostPrice.log',true);
echo "Done";
function getPriceArray($product,$priceArray){
    Mage::log('Product Id. :'.$product->getId(),Zend_Log::DEBUG,'setPostPrice.log',true);
    $previousPrices = $product->getData('group_price');
    Mage::log('Previous Price: '.json_encode($previousPrices,true),Zend_Log::DEBUG,'setPostPrice.log',true);
    foreach ($previousPrices as $prevPrice){
        if(isset($prevPrice['cust_group'])){
            if($prevPrice['cust_group'] != '0' && $prevPrice['cust_group'] != '2'){
                array_push($priceArray,$prevPrice);
            }
        }
    }
    Mage::log('New Price: '.json_encode($priceArray,true),Zend_Log::DEBUG,'setPostPrice.log',true);

    return $priceArray;
}
function savePrice($product,$groupPricingData){

    try {
        $product->setData('group_price', $groupPricingData);
       // $product->save();
    }catch (Exception $ex){
        Mage::log('Exception While Saving:'.$ex->getMessage(),Zend_Log::DEBUG,'setPostPrice.log',true);
    }
}


