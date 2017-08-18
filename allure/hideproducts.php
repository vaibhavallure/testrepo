<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;
$category = $_GET['category'];
if(empty($category)){
    die('Please provide category Id');
}
$collection = Mage::getModel('catalog/category')->load($category)
->getProductCollection()
->addAttributeToSelect('*') // add all attributes - optional
->addAttributeToFilter('status', 1);
$product_ids=$collection->getAllIds();
$action = Mage::getModel('catalog/resource_product_action');
echo "<pre>";
$stores=Mage::getModel('core/store')->getCollection();
$stores->addFieldToFilter('is_active',1);

foreach ($stores as $store){
    $action->updateAttributes($product_ids, array(
        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
    ),$store->getStoreId());
}
$action->updateAttributes($product_ids, array(
    'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
),0);

echo "Done";