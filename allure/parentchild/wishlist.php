<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
echo "<pre>";

$customer=Mage::getModel('customer/customer')->load(32808); 
$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
$wishListItemCollection = $wishlist->getItemCollection();

foreach ($wishListItemCollection as $item){
    $oldProduct=Mage::getModel("catalog/product")->load($item->getProductId());
    print_r($oldProduct->getSku());
    echo "<br>";
}

print_r($wishListItemCollection->getData());

