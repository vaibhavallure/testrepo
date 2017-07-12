<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);


require_once('../app/Mage.php');
umask(0);
Mage::app();

//$api = Mage::getSingleton('core/store_api_v2');

//$storeList = $api->items();

//var_dump($storeList);die;

//var_dump($session);