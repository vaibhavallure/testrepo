<?php

require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
$rand_number = rand(1,9);

$sku_list = array('XTHBF',
    'XTHBF6',
    'XTHD4',
    'XTHD2',
    'ZPBF6R0',
    'XTHBF25D',
    'XTHBF2D',
    'XTHMQD');

$totalCount = 0;
$write_line = 'SKU,PRODUCT NAME,PRICE,PRICE FOR NOT LOGGED IN,PRICE FOR GENERAL,PRICE FOR WHOLESALE'.PHP_EOL;
echo $write_line;
writeData($write_line,$rand_number);
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

        /*Set Allowed group to parent*/
        $parentProduct = Mage::getModel('catalog/product')
            ->load($parentId);

        setPriceData($parentProduct,$rand_number);

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
                    setPriceData($childProduct,$rand_number);
                    $totalCount++;
                }
                $childrenCount++;
            }
        }
    }
    echo "Data in ".$fileName = 'PriceList_'.date('m-d-Y').$rand_number.'_.csv';
}

function setPriceData($product,$rand_number){
    $sku = $product->getSku();
    $name = $product->getName();
    $price = $product->getPrice();
    $not_logged_in_price = $general_price = $wholesale_price = '';
    $previousPrices = $product->getData('group_price');
    foreach ($previousPrices as $data){
        if(isset($data['cust_group'])){
            if($data['cust_group'] == 0){
                $not_logged_in_price = $data['price'];
            }elseif($data['cust_group'] == 1){
                $general_price = $data['price'];
            }elseif ($data['cust_group'] == 2){
                $wholesale_price = $data['price'];
            }
        }
    }
    $write_line = $sku.','.$name.','.$price.','.$not_logged_in_price.','.$general_price.','.$wholesale_price.PHP_EOL;
    echo $write_line;
    writeData($write_line,$rand_number);
}
function writeData($data,$rand_number){
    $fileName = 'PriceList_'.date('m-d-Y').$rand_number.'_.csv';
    $fp = fopen($fileName,"a");
    fwrite($fp,$data);
    fclose($fp);
}
