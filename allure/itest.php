<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);


require_once('../app/Mage.php');
umask(0);
Mage::app('london');

$stock = Mage::getSingleton('cataloginventory/stock');
$stock_item = Mage::getSingleton('cataloginventory/stock_item');
var_dump($stock->getId());
var_dump($stock_item->getStockId());
var_dump(get_class($stock));
var_dump(get_class($stock_item));

//$api = Mage::getSingleton('core/store_api_v2');

//$storeList = $api->items();

//var_dump($storeList);die;

//var_dump($session);